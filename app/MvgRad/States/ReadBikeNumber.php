<?php

declare (strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\MvgRad\Api\MvgRadModule;
use Finite\Event\TransitionEvent;
use Swagger\Client\Model\ModelInterface;

class ReadBikeNumber extends MvgRadState implements MvgRadStateInterface
{
    private array $dtmfSequence = [];

    public function handleAusleihe(AppController $appController): void
    {
        $radnummer = implode($this->dtmfSequence);

        $channelID = $appController->getChannelID();
        $channelsApi = $appController->ari->channels();
        MvgRadModule::sayDigits($radnummer, $channelID, $channelsApi);
        $pin = $this->mvgRadApi->doAusleihe($radnummer);
        MvgRadModule::sayDigits($pin, $channelID, $channelsApi);
        $this->sm->done($this);
    }

    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function onEvent(AppController $appController, AppFreeDto|ModelInterface $dto): void
    {
        print(__CLASS__ . "->onEvent(" . json_encode($dto));
        if ($dto instanceof ChannelDtmfReceived) {
            $this->addDtmf($dto->digit);
        }

        if (count($this->dtmfSequence) === 5) {
            $this->handleAusleihe($appController);
        }

        return;
    }

    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement begin() method.
        return null;
    }    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement begin() method.
        return null;
    }
}
