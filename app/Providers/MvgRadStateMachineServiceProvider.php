<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\Api\MvgRadModule;
use AppFree\MvgRad\MvgRadArrayLoader;
use AppFree\MvgRad\MvgRadStateMachine;
use AppFree\MvgRad\MvgRadStateMachineLoader;
use Finite\Event\TransitionEvent;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MvgRadStateMachineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MvgRadStateMachine::class, function (Application $app) {
            /** @var StateMachineInterface $sm */
            $sm = new MvgRadStateMachine();
            $l = new MvgRadArrayLoader(MvgRadStateMachineLoader::definition($sm, $app->get(MvgRadApi::class), $app->get(MvgRadModule::class)));

//            $sm->setObject($appController);
            $l->load($sm);
            $sm->getDispatcher()->addListener("finite.post_transition", function (TransitionEvent $e) {
                /** @var AppController $appController */
                $appController = resolve(AppController::class);
                $eventDto = $e->getProperties()[MvgRadStateMachineLoader::DTO];
                if ($eventDto) {
                    $appController->receive($eventDto);
                }
            });

            return $sm;
        });
    }
}
