<?php

namespace AppFree\appfree\modules\MvgRad\Api\Prod;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;
use AppFree\Models\MvgRadUser;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MvgRadApi implements MvgRadApiInterface
{
    public const string VEHICLE_TYPE_BIKE = "BIKE";
    private ?\App\Models\User $user;
    private MvgRadUser $mvgRadUser;
    private \AppFree\Models\MvgRadApi $apiRecord;

    public function __construct(private AppFreeStateMachine $sm)
    {
        $this->user = $sm->getContext()->user;
        $this->mvgRadUser = $sm->getContext()->user->mvgRadUser;
        $this->apiRecord = \AppFree\Models\MvgRadApi::first();
    }

    public function doAusleihe(string $radnummer): string
    {
        $apiRecord = $this->apiRecord;

        $response = Http::withHeaders([
            'x-api-key' => $apiRecord->api_key,
            'x-api-client-os' => $apiRecord->api_client_os,
            'x-api-client-os-version' => $apiRecord->api_client_os_version,
            'x-api-client-version' => $apiRecord->api_client_version,
            'x-api-client-device' => $apiRecord->api_client_device,
            'x-api-build-info' => $apiRecord->api_build_info,
        ])->post('https://mvgo-gateway.app.mvg.de/v2/sharing/rad/rental/rent', [
            'idForRental' => $radnummer,
            'vehicleType' => self::VEHICLE_TYPE_BIKE,
        ]);

        $pin = $response["code"];

        $this->mvgRadUser->last_radnummer = $radnummer;
        $this->mvgRadUser->last_pin = $pin;
        $this->mvgRadUser->save();

        // fixme https://app.asana.com/0/1209569073764265/1209630441106281 - was ist der richtige pfad im json?
        return $pin;
    }

    public function getAusleiheRadnummer(): ?string
    {
        return $this->mvgRadUser->last_radnummer;
    }

    public function getPin(): ?string
    {
        return $this->mvgRadUser->last_pin;
    }

    public function doRueckgabe(): ?string
    {
        throw new RuntimeException("Bike return not possible with this API, must be performed on the bike itself.");
    }

    public function isMock(): bool
    {
        return false;
    }
}
