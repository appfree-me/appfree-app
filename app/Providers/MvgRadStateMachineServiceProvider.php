<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\appfree\modules\MvgRad\Api\Prod\MvgRadApi;
use AppFree\appfree\modules\MvgRad\MvgRadArrayLoader;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachineLoader;
use Finite\Event\TransitionEvent;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MvgRadStateMachineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MvgRadStateMachine::class, function (Application $app) {
            /** @var StateMachineInterface $sm */
            $sm = new MvgRadStateMachine();
            $mvgRadApi = match (config('app.mvg-rad-api')) {
                'prod' => new MvgRadApi($sm),
                'mock' => new \AppFree\appfree\modules\MvgRad\Api\Mock\MvgRadApi($sm),
                default => throw new \RuntimeException("env MVG_RAD_API must be ('mock','prod')"),
            };

            $l = new MvgRadArrayLoader(MvgRadStateMachineLoader::definition($sm, $mvgRadApi, $app->get(MvgRadModule::class)));

            $l->load($sm);

            // Used to give the previous state a way to pass a message to the next state
            $sm->getDispatcher()->addListener("finite.post_transition", function (TransitionEvent $e) {
                /** @var AppController $appController */
                $appController = resolve(AppController::class);
                $eventDto = $e->getProperties()[MvgRadStateMachineLoader::DTO];
                if ($eventDto) {
                    // Inject given DTO as first event for new state
                    $appController->receive($eventDto);
                }
            });

            return $sm;
        });
    }
}
