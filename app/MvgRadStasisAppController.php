<?php
declare(strict_types=1);

namespace AppFree;

use Devristo\Phpws\Client\WebSocket;
use Evenement\EventEmitter;
use Exception;
use Pest;
use PhpAri;
use React\EventLoop\LoopInterface;
use Zend\Log\Logger;

class MvgRadStasisAppController
{
    public WebSocket $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $stasisLogger;
    protected PEST $ariEndpoint;
    public PhpAri $phpariObject;
    private array $stasisChannelIDs = [];
    private array $dtmfSequence;
    public MvgRadModule $mvgRadApi;
    private EventEmitter $stasisEvents;

    public function __construct(string $appname)
    {
        $this->phpariObject = new PhpAri($appname);

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


