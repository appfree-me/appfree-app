<?php
declare(strict_types=1);


namespace AppFree\appfree;

use App\Models\User;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use Finite\StatefulInterface;
use Swagger\Client\Api\ChannelsApi;

class StateMachineContext implements StatefulInterface
{
    const ASTERISK_MAX_VAR_LENGTH = 255;
    public readonly string $channelId;
    private ?string $state = null;
    public ChannelsApi $channelsApi;
    public readonly ?User $user;
    private readonly Caller $caller;

    private ConvenienceApi $api;

    public function __construct(string $channelId, ChannelsApi $channelsApi, Caller $caller, ?User $user)
    {
        $this->channelId = $channelId;
        $this->channelsApi = $channelsApi;
        $this->user = $user;
        $this->caller = $caller;
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

    public function getUserMobilePhone(): ?string
    {
        return $this?->user?->mobilephone;
    }

    public function getCallerPhoneNumber(): string
    {
        return $this->caller->number;
    }

}
