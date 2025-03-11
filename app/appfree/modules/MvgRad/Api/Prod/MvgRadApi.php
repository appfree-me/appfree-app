<?php

namespace AppFree\appfree\modules\MvgRad\Api\Prod;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;
use AppFree\Models\MvgradTransaction;
use AppFree\Models\MvgRadUser;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MvgRadApi implements MvgRadApiInterface
{
    public const string VEHICLE_TYPE_BIKE = "BIKE";
    public const API_ID = 'MVG_PROD';
    private ?\App\Models\User $user;
    private MvgRadUser $mvgRadUser;
    private \AppFree\Models\MvgRadApi $apiRecord;

    public function __construct(private AppFreeStateMachine $sm)
    {

    }

    public function init(): void
    {
        //fixme
        $this->user = $this->sm->getContext()->user;
        $this->mvgRadUser = $this->sm->getContext()->user->mvgRadUser;
        $this->apiRecord = \AppFree\Models\MvgRadApi::first();
    }

    public function doAusleihe(string $radnummer, ?string $mockPin): string
    {

        $this->init();

        $apiRecord = $this->apiRecord;

        $response = Http::withHeaders([
            'User-Agent' => $apiRecord->user_agent,
            'X-Api-Build-Info' => $apiRecord->api_build_info,
            'X-Api-Client-Device' => $apiRecord->api_client_device,
            'X-Api-Client-OS' => $apiRecord->api_client_os,
            'X-Api-Client-OS-Version' => $apiRecord->api_client_os_version,
            'X-Api-Client-Version' => $apiRecord->api_client_version,
            'X-Api-Key' => $apiRecord->api_key,
            'X-Api-Session' => $this->mvgRadUser->session_token,
        ])->post('https://mvgo-gateway.web.azrapp.swm.de/v2/sharing/rad/rental/rent', [
//        ])->post('http://localhost:12345', [
            'idForRental' => $radnummer,
            'vehicleType' => self::VEHICLE_TYPE_BIKE,
        ])->json();

        //        $response = json_decode('{
        //  "id": "RAD-f4f03b4c-9a92-4cec-a3f3-9d760b40f09a",
        //  "code": "7529",
        //  "startDate": "2025-03-11T16:18:18.000Z"
        //}', true);

        $pin = $response["code"];

        $this->mvgRadUser->last_radnummer = $radnummer;
        $this->mvgRadUser->last_pin = $pin;
        $this->mvgRadUser->save();

        $this->addMvgRadTransaction($response);

        // fixme https://app.asana.com/0/1209569073764265/1209630441106281 - was ist der richtige pfad im json?
        return $pin;
    }

    private function addMvgRadTransaction(array $response): void
    {
        $tx = new MvgradTransaction();
        $tx->user_id = $this->user->id;
        $tx->type = "rental";
        $tx->pin = $response["code"];
        $tx->api_id = self::API_ID;
        $tx->mvg_id = $response["id"];
        $tx->mvg_start_date = $response["startDate"];

        $tx->save();
    }

    public function getAusleiheRadnummer(): ?string
    {
        $this->init();

        return $this->mvgRadUser->last_radnummer;
    }

    public function getPin(): ?string
    {
        $this->init();

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
