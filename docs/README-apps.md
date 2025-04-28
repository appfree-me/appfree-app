# Overview
This document describes how an Appfree Module works with the example of MVG Rad Module.

An Appfree module provides the business logic and user interface for an Appfree app. 
Currently, the only supported module is the MVG Rad module. 
Eventually, modules will be included via composer. 

The module for MVG Rad is located at `app/appfree/modules/MvgRad`.

# Call Manager
The Call Manager is contained in `app/AppController.php`.

Its job is to coordinate incoming calls: When a call comes in, the state machine of the MVG Rad module will be initialized. A new state machine with its dependencies will be instantiated and registered. Events for the new call will be passed to it. When the call ends, it will be unregistered and destroyed. 

## MVG Rad

MVG Rad is currently the only existing appfree module. 

### Module components

The MVG Rad appfree module consists of the following parts:

| Name                     |  Path                                                      |
|--------------------------|------------------------------------------------------------|
| State machine definition |  `app/appfree/modules/MvgRad/MvgRadStateMachineLoader.php` |
| State machine            |  `app/appfree/modules/MvgRad/MvgRadStateMachine.php`       |
| States for state machine |  `app/appfree/modules/MvgRad/States/Begin.php`             |
| MVG API interfaces       |  `app/appfree/modules/MvgRad/Api/`                         |
| MVG Rad DTOs             |  `app/AppFreeCommands/MvgRad/`                             |


### DTOs

DTOs are used extensively: 

| Description                   | Path                                                                                         |
|-------------------------------|----------------------------------------------------------------------------------------------|
| Events Received from Asterisk | `app/AppFreeCommands/Stasis/Events/`                                                         |
| DTOs passed between states    | `app/AppFreeCommands/AppFree/Commands/StateMachine/`, `app/AppFreeCommands/MvgRad/Commands/` |

### State Machine

The MVG Rad state machine is defined in `MvgRadStateMachineLoader.php` This file defines mainly the initial state and the allowed transitions. States are referenced by their class names, e. g. `Begin::class`.
The state machine will be in its initial state when a new call comes in.  
The currently  active state will receive events concering the active call, e. g. User Input DTMF and call begin (`StasisStart`) and end events. 
A state is responsible for handling a specific part of the call. The `Begin::class` state (defined in `app/appfree/modules/MvgRad/States/Begin.php`) is responsible for greeting the user and speaking the PIN for an active rental. When it is done, it transitions into a new state responsible for handling a new rental.

#### Initialization

The state machine is initialized in `app/Providers/MvgRadStateMachineServiceProvider.php` when a new call comes in.

#### Dependencies 

Dependencies for the state machine are also defined in this service provider. This includes the MVGRadApi and access to Asterisk commands.

#### Call flow

During a call, the following loop will be active: 
``` 
Receive Asterisk Event via Websocket => send event to run() function of the currently active state
``` 
The run() function will typically send commands to Asterisk via the Asterisk REST API.

#### States

A state consists of the following parts:
- A *generator function* called `run`. This is the main business logic function in each state. Everything happens here.
- The run function will be called when a new event comes in, do some processing and then pause itself and wait for a new event.
- Introduction to generator functions is available [here](https://medium.com/@erlandmuchasaj/php-generators-b26e98025a50) 
- When the run function is executed fully, it is responsible for transitioning to a new state. The run() function of that state will be called when a new event comes in.

#### Yielding expectations 

When a part of the `run()` function waits for a new event, it interrupts execution and hands control back to the main application. It does this by calling `yield`. 
Please read the introduction to Generator functions to get accustomed to how yield works. 
Yield allows to pass data back to the main application. By calling 

```
        yield "expect" => StasisStart::class;
```

the `Begin` state tells the main application to restart it again only when the `StasisStart` event is received.

By yielding multiple times and `expect`ing certain events, the call flow for this state is defined.

#### Yielding callbacks 

States can also `yield` commands. This is used to transition to a new state once a new event comes in. E. g. the `Begin` state transitions to the `ReadDtmfString` to enable the user to enter a bike number: 
```
            yield "callback" => function () use ($ctx) {
                $this->sm->done(
                    ReadDtmfString::class,
                    ReadDtmfString::dto(...)
                    );
```

The first argument to the `done()` function declares the name of the target state that should be entered (`ReadDtmfString`).
The second argument defines a DTO which is passed to the target state as its first event. This is used to pass data to the next state. 

#### State transitions
Currently, allowed transitions are defined statically in the module's loader: `app/appfree/modules/MvgRad/MvgRadStateMachineLoader.php`

To allow a transition between states, add the required states to the 'transitions' array returned from `definition()`. Trying to transition to different states will result in an error. 


## Notes
### Audio Alerts

Alerts are currently static and generated with https://ttsmaker.com/.
To deploy them, add an MP3 or Ogg Opus file to `dist/usr/share/asterisk/sounds/en_US_f_Allison` in `phone-server` project and redeploy the `phone-server` project.
