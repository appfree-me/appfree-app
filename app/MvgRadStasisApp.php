<?php
//namespace app;

class MvgRadStasisApp
{

    private PEST $ariEndpoint;
    private \Devristo\Phpws\Client\WebSocket $stasisClient;
    private \React\EventLoop\LoopInterface $stasisLoop;
    private phpari $phpariObject;
    private int $stasisChannelID;
    private array $dtmfSequence;

    private MvgRadApi $mvgRadApi;

    public \Zend\Log\Logger $stasisLogger;
    private \Evenement\EventEmitter $stasisEvents;

    public function __construct(string $appname)
    {

        $this->phpariObject = new phpari($appname);

        $this->ariEndpoint = $this->phpariObject->ariEndpoint;
        $this->stasisClient = $this->phpariObject->stasisClient;
        $this->stasisLoop = $this->phpariObject->stasisLoop;
        $this->stasisLogger = $this->phpariObject->stasisLogger;
        $this->stasisEvents = $this->phpariObject->stasisEvents;

        $this->mvgRadApi = new MvgRadApi();
    }

    public function setDtmf(string $digit): void
    {
            $this->dtmfSequence[] = $digit;
    }

    public function part1() {
        $this->phpariObject->channels()->channel_answer($this->stasisChannelID);
        $this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:mvg-greeting');
        if ($this->hasLastPin()) {
            $this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:mvg-last-pin-is');
        }
        $this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:mvg-pin-prompt');

    }

    public function part2() {
        // DTMF should now be available
        $this->doAusleihe(implode($this->dtmfSequence));
    }

    private function doAusleihe(string $radnummer)
    {
        $this->mvgRadApi->doAusleihe($radnummer);
    }


    // process stasis events
    public function StasisAppEventHandler(): void
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
            $this->stasisLogger->notice("Event received: StasisStart");
            $this->stasisLogger->notice(json_encode($event->channel));
            $this->stasisChannelID = $event->channel->id;
            $this->part1();

        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            /*
             * The following section will produce an error, as the channel no longer exists in this state - this is intentional
             */
            $this->stasisLogger->notice("Event received: StasisEnd");
            if (!$this->phpariObject->channels()->channel_delete($this->stasisChannelID)) $this->stasisLogger->notice("Error occurred: " . $this->phpariObject->lasterror);
        });


        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackStarted +++ " . json_encode($event->playback) . "\n");
        });

        $this->stasisEvents->on('PlaybackFinished', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackFinished +++ " . json_encode($event->playback) . "\n");
//            switch ($event->playback->id) {
//                case "play1":
//                    $this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:demo-congrats', NULL, NULL, NULL, 'play2');
//                    break;
//                case "play2":
//                    $this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:demo-echotest', NULL, NULL, NULL, 'end');
//                    break;
//                case "end":
//                    $this->phpariObject->channels()->channel_continue($this->stasisChannelID);
//                    break;
//            }
        });

        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            $this->setDtmf($event->digit);
            $this->stasisLogger->notice("+++ DTMF Received +++ [" . $event->digit . "]"  . json_encode($this->dtmfSequence) . "\n");
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
                $this->stasisLogger->notice('Received event: ' . $event->type);
                $this->stasisEvents->emit($event->type, array($event));
            });

    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
            $this->stasisClient->open();
            $this->stasisLoop->run();
    }

    private function hasLastPin(): bool
    {
        return false;
    }


}

$basicAriClient = new MvgRadStasisApp("appfree");

$basicAriClient->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
$basicAriClient->StasisAppEventHandler();

$basicAriClient->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
$basicAriClient->StasisAppConnectionHandlers();

$basicAriClient->stasisLogger->info("Connecting... Waiting for handshake...");
$basicAriClient->execute();

exit(0);
