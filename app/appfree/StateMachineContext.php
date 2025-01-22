<?php
declare(strict_types=1);


namespace AppFree\appfree;

use App\Models\User;
use Finite\StatefulInterface;
use Swagger\Client\Api\ChannelsApi;

class StateMachineContext implements StatefulInterface
{
    const ASTERISK_MAX_VAR_LENGTH = 255;
    public readonly string $channelId;
    private ?string $state = null;
    public ChannelsApi $channelsApi;
    private ?User $user;

    private ConvenienceApi $api;

    public function __construct(string $channelId, ChannelsApi $channelsApi, ?User $user)
    {
        $this->channelId = $channelId;
        $this->channelsApi = $channelsApi;
        $this->user = $user;
        $this->api = new ConvenienceApi($channelId, $channelsApi, $user);
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

        return $this->api->play($media);
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
    public function sayDigits(string $digitString): ?string
    {
        return $this->api->sayDigits($digitString);
    }

    public function hangup()
    {
        $this->channelsApi->hangup($this->channelId);
    }

    public function getMobilePhone(): ?string
    {
        return $this?->user?->mobilephone;
    }
}
