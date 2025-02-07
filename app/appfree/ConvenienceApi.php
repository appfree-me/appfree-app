<?php
declare(strict_types=1);


namespace AppFree\appfree;

use App\Models\User;
use AppFree\AppController;
use Monolog\Logger;
use Swagger\Client\Api\ChannelsApi;
use Swagger\Client\ApiException;

class ConvenienceApi
{
    public readonly string $channelId;
    public ChannelsApi $channelsApi;
    private ?User $user;

    public function __construct(string $channelId, ChannelsApi $channelsApi, $user)
    {
        $this->channelId = $channelId;
        $this->channelsApi = $channelsApi;
        $this->user = $user;
    }

    public function play(string|array $media): string
    {
        if (gettype($media) === "string") {
            $media = [$media];
        }

        $playbackId = $this->getRandomId() . substr(implode(",", $media), 0, StateMachineContext::ASTERISK_MAX_VAR_LENGTH - 50);

        try {
            $this->channelsApi->play($this->channelId, $media, null, null, null, $playbackId);
        }
            // Only should catch "channel not found" - fixme
            // Sometimes channels just disappear, we don't want to crash the entire app
        catch (ApiException $exception) {
            resolve(Logger::class)->error("play api exception for channel $this->channelId: " . $exception->getMessage());
            resolve(AppController::class)->removeStateMachine($this->channelId);
            return "channelRemoved_$this->channelId";
        }

        return $playbackId;
    }

    /**
     * Playback some digits and return the playback id of the last played back digit.
     */
    public function sayDigits(string $digitString): ?string
    {
        $playbackId = null;

        foreach (str_split($digitString) as $digit) {
            $playbackId = $this->play("sound:$digit");
        }

        return $playbackId;
    }

    /**
     * @return string
     */
    public function getRandomId(): string
    {
        return bin2hex(random_bytes(4));
    }
}
