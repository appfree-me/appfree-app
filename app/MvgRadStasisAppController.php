<?php
declare(strict_types=1);

namespace AppFree;

use Devristo\Phpws\Client\WebSocket;
use Evenement\EventEmitter;
use Exception;
use Pest;
use phpari;
use React\EventLoop\LoopInterface;
use Zend\Log\Logger;

class MvgRadStasisAppController
{
    public WebSocket $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $stasisLogger;
    protected PEST $ariEndpoint;
    private phpari $phpariObject;
    private array $stasisChannelIDs = [];
    private array $dtmfSequence;
    private MvgRadModule $mvgRadApi;
    private EventEmitter $stasisEvents;

    public function __construct(string $appname)
    {
        $this->phpariObject = new phpari($appname);

        $this->ariEndpoint = $this->phpariObject->ariEndpoint;
        $this->stasisClient = $this->phpariObject->stasisClient;
        $this->stasisLoop = $this->phpariObject->stasisLoop;
        $this->stasisLogger = $this->phpariObject->stasisLogger;
        $this->stasisEvents = $this->phpariObject->stasisEvents;

        $this->mvgRadApi = new MvgRadModule($this);
    }

    public function init(): void
    {
        $this->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();

        $this->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
    }

    public function sayDigits(string $digitString): void
    {
        foreach (str_split($digitString) as $digit) {
            $this->phpariObject->channels()->channel_playback($this->getChannelID(), "sound:digits/$digit", null, null, null);
        }
    }

    public function StasisAppEventHandler(): void
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
            $this->addChannel($event->channel->id);
            $this->startChannel();
        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            $this->removeChannel($event->channel->id);
        });

        $this->stasisEvents->on('ChannelChangeState', function ($event) {
            $this->stasisLogger->notice("+++ ChannelChangeState +++ " . json_encode($event) . "\n");
        });


        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackStarted +++ " . json_encode($event->playback) . "\n");
        });

        $this->stasisEvents->on('PlaybackFinished', function ($event) {
//            $this->stasisLogger->notice("+++ PlaybackFinished +++ " . json_encode($event->playback->id) . "\n");
        });



        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            $this->addDtmf($event->digit);
            $this->stasisLogger->notice("+++ DTMF Received +++ [" . $event->digit . "]" . json_encode($this->dtmfSequence) . "\n");
            switch ($event->digit) {
                case "*":
                    $this->dtmfSequence = [];
                    $this->stasisLogger->notice("+++ Resetting DTMF buffer\n");
                    break;
                case "#":
                    $this->stasisLogger->notice("+++ Ending DTMF input " . $this->phpariObject->playbacks()->get_playback());


                    // Dialplan context "appfree"?
                    // Was bedeutet channel_continue()?
                    // $this->phpariObject->channels()->channel_continue($this->stasisChannelID, "appfree", "s", 1);
//                    $this->mvgRadApi->part2($this->dtmfSequence);
                    break;
                default:
                    break;
            }
        });
    }

    private function addChannel(string $channelId): void
    {
  /*      if (count($this->stasisChannelIDs)) {
            $this->denyChannel($channelId);
            return;
        }*/
        $this->stasisChannelIDs[] = $channelId;
    }

    private function removeChannel($id)
    {
        $this->stasisChannelIDs = array_diff($this->stasisChannelIDs, [$id]);
    }


    // endHandler should not be needed as asterisk deletes channels by itself after client hangup
//    public function endHandler(): void
//    {
//        foreach ($this->stasisChannelIDs as $index=>$channelID) {
//            echo "Deleting Channel $channelID \n";
//            if (!$this->phpariObject->channels()->channel_delete($channelID)) $this->stasisLogger->notice("Error occurred: " . $this->phpariObject->lasterror);
//            array_splice($this->stasisChannelIDs, $index, 1);
//        }
//    }
    // process stasis events

    private function denyChannel(string $channelId): void
    {
        $this->stasisLogger->err("Channel $channelId denied");
        $this->phpariObject->channels()->channel_playback($channelId, 'media:please-try-call-later', null, null, null, "channel-denied");
        sleep(2);
        $this->phpariObject->channels()->channel_continue($channelId);
    }

    public function startChannel(): void
    {
        $this->phpariObject->channels()->channel_ringing_start($this->getChannelID());
        sleep(1);
        $this->phpariObject->channels()->channel_answer($this->getChannelID());
        $this->stasisLogger->notice("channel_playback() play1 " . $this->getChannelID());
//        $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:demo-thanks', NULL, NULL, NULL, 'play1');

//        $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:demo-thanks', NULL, NULL, NULL, "play1");
//        if ($this->hasLastPin()) {
//            $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-last-pin-is', NULL, NULL, null, "play2");
//        }
        $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-greeting', null, null, null, "play2");
        if ($this->mvgRadApi->hasLastPin()) {
            $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-last-pin-is', null, null, null, "play3");
            $this->sayDigits("123");
        }
        $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-pin-prompt', null, null, null, "play4");
    }

    public function getChannelID(): string
    {
        return $this->stasisChannelIDs[0];
    }



    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }

    public function StasisAppConnectionHandlers(): void
    {
        $this->stasisClient->on("request", function ($headers) {
            $this->stasisLogger->notice("Request received!");
        });

        $this->stasisClient->on("handshake", function () {
            $this->stasisLogger->notice("Handshake received!");
        });

        $this->stasisClient->on("message", function ($message) {
            $event = json_decode($message->getData());
            $this->stasisLogger->notice('Received message: ' . $event->type . ", data: " . $message->getData());
            $this->stasisEvents->emit($event->type, array($event));
        });
    }
}


