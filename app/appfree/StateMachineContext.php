<?php
declare(strict_types=1);


namespace AppFree\appfree;

use Finite\StatefulInterface;
use Swagger\Client\Api\ChannelsApi;
use Swagger\Client\ApiException;

class StateMachineContext implements StatefulInterface
{
    public string $channelId;
    private ?string $state = null;
    public ChannelsApi $channelsApi;

    public function __construct(string $channelId, ChannelsApi $channelsApi)
    {
        $this->channelId = $channelId;
        $this->channelsApi = $channelsApi;
    }

    public function getFiniteState()
    {
        return $this->state;
    }

    public function setFiniteState($state)
    {
        $this->state = $state;
    }

    // Convenience functions

    public function play(string|array $media): string
    {
        if (gettype($media) === "string") {
            $media = [$media];
        }

        $playbackId = uniqid($media);
        $this->channelsApi->play($this->channelId, $media, null, null, null, $playbackId);

        return $playbackId;
    }

    public function ring()
    {
        $this->channelsApi->ring($this->channelId);
    }

    public function answer()
    {
        $this->channelsApi->answer($this->channelId);
    }

    /**
     * Playback some digits and return the playback id of the last played back digit.
     */
    public function sayDigits(string $digitString): string
    {
        foreach (str_split($digitString) as $digit) {
            $playbackId = uniqid(__METHOD__);
            $this->channelsApi->play($this->channelId, ["sound:digits/$digit"], null, null, null, $playbackId);
        }
        return $playbackId;
    }

    public function hangup()
    {
        $this->channelsApi->hangup($this->channelId);
    }
}
