<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Expectations;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;

class PlaybackFinishedExpectation extends Expectation
{
    public function __construct(private readonly ?string $playbackId)
    {
    }

    public function hasMatch(AppFreeDto $inputDto): bool
    {
        if (!$inputDto instanceof PlaybackFinished) {
            return false;
        }

        if ($this->playbackId !== null) {
            return $inputDto->playback->id === $this->playbackId;
        }

        return false;
    }
}
