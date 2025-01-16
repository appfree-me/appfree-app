<?php
declare(strict_types=1);

namespace AppFree\Ari;

use AppFree\Ari\Interfaces\EventReceiverInterface;
use AppFree\MakeDto;
use Evenement\EventEmitterInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\GuClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\DataInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Swagger\Client\Api\ApplicationsApi;
use Swagger\Client\Api\AsteriskApi;
use Swagger\Client\Api\BridgesApi;
use Swagger\Client\Api\ChannelsApi;
use Swagger\Client\Api\DeviceStatesApi;
use Swagger\Client\Api\EndpointsApi;
use Swagger\Client\Api\MailboxesApi;
use Swagger\Client\Api\PlaybacksApi;
use Swagger\Client\Api\RecordingsApi;
use Swagger\Client\Api\SoundsApi;


/**
 * phpari - A PHP Class Library for interfacing with Asterisk(R) ARI
 * by Laurent Pichler, based on work by Nir Simionovich
 */
class PhpAri
{
    public const EVENT_NAME_APPFREE_MESSAGE = 'appfreedto.message';

    private PhpAriConfig $config;
    public Logger $logger;
    public LoopInterface $stasisLoop;
    public PromiseInterface $stasisClient;
    public bool $isDebug;
    public string $logfile;
    public Client $ariEndpoint;
    public string $baseUri;
    private string $appName;
    private EventEmitterInterface $emitter;
//    public $i = 0;

    public function __construct(string $appName, EventEmitterInterface $emitter, PhpAriConfig $phpAriConfig, Client $client, Logger $logger)
    {
        try {
            /* Get our configuration */
            $this->config = $phpAriConfig;
            $this->logger = $logger;

            /* Some general information */
            $this->isDebug = (bool)$this->config->general['debug'];
            $this->logfile = $this->config->general['logfile'];
            $this->emitter = $emitter;
            $this->ariEndpoint = $client;

            $this->appName = $appName;

            /* Connect to ARI server */
            $this->init();
        } catch (Exception $e) {
            die("Exception raised: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    /**
     * This function is connecting and returning a phpari client object which
     * transferred to any of the interfaces will assist with the connection process
     * to the Asterisk Stasis or to the Asterisk 12 web server (Channels list , End Points list)
     * etc.
     */


    public function init(): PromiseInterface
    {
        try {
            $config = $this->config;
            $config_asterisk = $config->asterisk_ari;

            if ($this->isDebug) {
                $this->logger->debug("Initializing WebSocket Information");
            }

            $promise = resolve(PromiseInterface::class);
            $promise->catch(function (Exception $err) {
                $this->logger->error($err->getCode() . $err->getMessage());
            });
            // todo anwendung sollte auf vebindungszusammenbruch reagieren


            $this->stasisLoop = Loop::get();
            $this->stasisClient = $promise;

            $this->logger->debug("Setup Events");
            $this->setupEvents($this->stasisClient);
            return $promise;
        } catch (Exception $e) {
            die("Exception raised: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    private function setupEvents(PromiseInterface $stasisClient): void
    {
//        $this->i++;
        $this->stasisClient->then(function (WebSocket $conn) {
            $conn->on("message", function (DataInterface $message) {
                $payload = json_decode($message->getPayload());
                $eventDto = MakeDto::make($payload);

                $this->emitter->emit(self::EVENT_NAME_APPFREE_MESSAGE, [$eventDto]);
            });
        });

        // ?
//        $this->stasisClient->then(function ($conn) {
//            $conn->on("request", function (DataInterface $message) {
//                $this->logger->notice(__FILE__ . "Request received!");
//            });
//        });

//        ??
//        $this->stasisClient->then(function ($conn) {
//            $conn->on("handshake", function (DataInterface $message) {
//                $this->logger->notice(__FILE__ . "Handshake received!");
//            });
//        });
    }



    private function initApi(string $fqcn)
    {
        return new $fqcn($this->ariEndpoint);
    }

    public function applications(): ApplicationsApi
    {
        return $this->initApi(ApplicationsApi::class);
    }

    public function asterisk(): AsteriskApi
    {
        return $this->initApi(AsteriskApi::class);
    }

    public function bridges(): BridgesApi
    {
        return $this->initApi(BridgesApi::class);
    }

    public function channels(): ChannelsApi
    {
        return $this->initApi(ChannelsApi::class);
    }

    public function devicestates(): DeviceStatesApi
    {
        return $this->initApi(DeviceStatesApi::class);
    }

    public function endpoints(): ?EndpointsApi
    {
        return $this->initApi(EndpointsApi::class);
    }

    public function events(): ?EndpointsApi
    {
        return $this->initApi(EndpointsApi::class);
    }

    public function mailboxes(): ?MailboxesApi
    {
        return $this->initApi(MailboxesApi::class);
    }

    public function recordings(): ?RecordingsApi
    {
        return $this->initApi(RecordingsApi::class);
    }

    public function sounds(): ?SoundsApi
    {
        return $this->initApi(SoundsApi::class);
    }

    public function playbacks(): ?PlaybacksApi
    {
        return $this->initApi(PlaybacksApi::class);
    }
}
