<?php
declare(strict_types=1);

namespace AppFree\AppFreeCommands\AppFree\Expectations;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use Illuminate\Support\Facades\App;

class PlaybackFinishedExpectation extends Expectation
{
    private string $playbackId;

    public function __construct(string $playbackId) {
        $this->playbackId = $playbackId;
    }

    public function hasMatch(AppFreeDto $inputDto): bool {

        if (! $inputDto instanceof PlaybackFinished) {
            return false;
        }

        return $inputDto->playback->id === $this->playbackId;
    }
}
