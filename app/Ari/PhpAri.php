<?php

declare(strict_types=1);

namespace AppFree\Ari;

use AppFree\MakeDto;
use Evenement\EventEmitterInterface;
use Exception;
use GuzzleHttp\Client;
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
    private array $apis =  [];

    public function __construct(string $appName, EventEmitterInterface $emitter, PhpAriConfig $phpAriConfig, Client $client, Logger $logger)
    {
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

    }

    /**
     * This function is connecting and returning a PhpAri client object which
     * transferred to any of the interfaces will assist with the connection process
     * to the Asterisk Stasis or to the Asterisk 12 web server (Channels list , End Points list)
     * etc.
     */


    public function init(): PromiseInterface
    {
        if ($this->isDebug) {
            $this->logger->debug("Initializing WebSocket Information");
        }

        //todo more specific argument to resolve
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
    }

    private function setupEvents(PromiseInterface $stasisClient): void
    {
        $this->stasisClient->then(function (WebSocket $conn) {
            $conn->on("message", function (DataInterface $message) {
                $payload = json_decode($message->getPayload());
                $eventDto = MakeDto::make($payload);

                $this->emitter->emit(self::EVENT_NAME_APPFREE_MESSAGE, [$eventDto]);
            });
        });

    }

    private function getApi(string $fqcn)
    {
        if (!isset($this->apis[$fqcn])) {
            $this->apis[$fqcn] = new $fqcn($this->ariEndpoint);
        }

        return $this->apis[$fqcn];
    }

    public function applications(): ApplicationsApi
    {
        return $this->getApi(ApplicationsApi::class);
    }

    public function asterisk(): AsteriskApi
    {
        return $this->getApi(AsteriskApi::class);
    }

    public function bridges(): BridgesApi
    {
        return $this->getApi(BridgesApi::class);
    }

    public function channels(): ChannelsApi
    {
        return $this->getApi(ChannelsApi::class);
    }

    public function devicestates(): DeviceStatesApi
    {
        return $this->getApi(DeviceStatesApi::class);
    }

    public function endpoints(): ?EndpointsApi
    {
        return $this->getApi(EndpointsApi::class);
    }

    public function events(): ?EndpointsApi
    {
        return $this->getApi(EndpointsApi::class);
    }

    public function mailboxes(): ?MailboxesApi
    {
        return $this->getApi(MailboxesApi::class);
    }

    public function recordings(): ?RecordingsApi
    {
        return $this->getApi(RecordingsApi::class);
    }

    public function sounds(): ?SoundsApi
    {
        return $this->getApi(SoundsApi::class);
    }

    public function playbacks(): ?PlaybacksApi
    {
        return $this->getApi(PlaybacksApi::class);
    }
}
