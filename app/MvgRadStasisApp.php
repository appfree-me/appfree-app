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

class MvgRadStasisApp
{
    protected PEST $ariEndpoint;
    public WebSocket $stasisClient;
    public LoopInterface $stasisLoop;
    private phpari $phpariObject;
    private array $stasisChannelIDs = [];
    private array $dtmfSequence;
    private MvgRadModule $mvgRadApi;
    public Logger $stasisLogger;
    private EventEmitter $stasisEvents;

    public function __construct(string $appname)
    {
        $this->phpariObject = new phpari($appname);

        $this->ariEndpoint = $this->phpariObject->ariEndpoint;
        $this->stasisClient = $this->phpariObject->stasisClient;
        $this->stasisLoop = $this->phpariObject->stasisLoop;
        $this->stasisLogger = $this->phpariObject->stasisLogger;
        $this->stasisEvents = $this->phpariObject->stasisEvents;

        $this->mvgRadApi = new MvgRadModule();
    }

    public function init(): void
    {
//        $this->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();

//        $this->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
    }

    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
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
        if($this->mvgRadApi->hasLastPin()){
            $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-last-pin-is', null, null, null, "play3");
            $this->sayDigits("123");
        }
        $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:mvg-pin-prompt', null, null, null, "play4");

    }

    public function part2(): void
    {
        // DTMF should now be available
        $pin = $this->doAusleihe(implode($this->dtmfSequence));
        $this->sayDigits($pin);
        //hangup
        $this->phpariObject->channels()->delete($this->getChannelID());

    }
    private function sayDigits(string $digitString): void
    {
        foreach (str_split( $digitString) as $digit) {
            $this->phpariObject->channels()->channel_playback($this->getChannelID(), "sound:digits/$digit", null, null, null);
        }
    }
    private function doAusleihe(string $radnummer): string
    {
        $this->stasisLogger->notice("Ausleihe Nummer $radnummer");
        return $this->mvgRadApi->doAusleihe($radnummer);
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
    public function StasisAppEventHandler(): void
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
//            $this->stasisLogger->notice("StasisStart event: " . json_encode($event));
//            $this->stasisLogger->alert('Adding Channel' . $event->channel->id);
            $this->addChannel($event->channel->id);
            $this->startChannel();
        });

//        $this->stasisEvents->on('StasisEnd', function ($event) {
//            /*
//             * The following section will produce an error, as the channel no longer exists in this state - this is intentional
//             */
//            $this->endHandler();
//        });

        $this->stasisEvents->on('ChannelChangeState', function($event){
                        $this->stasisLogger->notice("+++ ChannelChangeState +++ " . json_encode($event) . "\n");

        });


        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackStarted +++ " . json_encode($event->playback) . "\n");
        });

//        $this->stasisEvents->on('PlaybackFinished', function ($event) {
//            $this->stasisLogger->notice("+++ PlaybackFinished +++ " . json_encode($event->playback) . "\n");
//            switch ($event->playback->id) {
//                case "play1":
//                    $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:demo', NULL, NULL, NULL, 'play2');
//                    break;
//                case "play2":
//                    $this->phpariObject->channels()->channel_playback($this->getChannelID(), 'sound:demo-echotest', NULL, NULL, NULL, 'end');
//                    break;
//                case "end":
//                    $this->phpariObject->channels()->channel_continue($this->getChannelID());
//                    break;
//            }
//        });

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
                    $this->part2();
                    break;
                default:
                    break;
            }
        });
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


    private function hasLastPin(): bool
    {
        return false;
    }

    public function getChannelID(): string
    {
        return $this->stasisChannelIDs[0];
    }

    private function addChannel(string $channelId): void
    {
        if (count($this->stasisChannelIDs)) {
            $this->denyChannel($channelId);
            return;
        }
        $this->stasisChannelIDs[] = $channelId;

    }

    private function denyChannel(string $channelId): void
    {
        $this->stasisLogger->err("Channel $channelId denied");
        $this->phpariObject->channels()->channel_playback($channelId, 'media:please-try-call-later',null, null, null, "channel-denied");
        sleep(2);
        $this->phpariObject->channels()->channel_continue($channelId);
    }


}


