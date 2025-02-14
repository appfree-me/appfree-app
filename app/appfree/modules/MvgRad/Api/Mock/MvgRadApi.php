<?php

namespace AppFree\appfree\modules\MvgRad\Api\Mock;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;
use AppFree\Models\MvgradMockState;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Support\Facades\DB;

class MvgRadApi implements MvgRadApiInterface
{
    public const API_ID = "mock";

    public function __construct(private AppFreeStateMachine $sm)
    {
    }

    public function doAusleihe(string $radnummer, string $mockPin = null): ?string
    {
        sleep(2);

        if ($this->getAusleiheRadnummer()) {
            return null;
        }

        if ($mockPin === null) {
            $mockPin = $this->generateMockPin();
        }

        if (!config('mvg.video_dreh')) {
            DB::table("mvgrad_mock_states")->updateOrInsert(
                [
                    'phone' => $this->sm->getContext()->getCallerPhoneNumber()
                ],
                [
                    'rental_state' => 'running',
                    'pin' => $mockPin,
                    'radnummer' => $radnummer,
                ]
            );
        }


        return $mockPin;
    }

    public function doRueckgabe(): ?string
    {
        sleep(2);

        $phone = $this->sm->getContext()->getCallerPhoneNumber();
        try {
            $radnummer = MvgradMockState::where('phone', '=', $phone, 'and')->where('rental_state', '=', 'running')->firstOrFail()->radnummer;
        } catch (RecordNotFoundException $e) {
            $radnummer = null;
        }

        DB::table("mvgrad_mock_states")->updateOrInsert(
            [
                'phone' => $this->sm->getContext()->getCallerPhoneNumber(),
            ],
            [
                'rental_state' => 'not-running',
                'pin' => '',
                'radnummer' => ''
            ]);


        return $radnummer;
    }

    public function getAusleiheRadnummer(): ?string
    {
        $phone = $this->sm->getContext()->getCallerPhoneNumber();

        $record = MvgradMockState::where('phone', '=', $phone, 'and')->where('rental_state', '=', 'running')->first();
        if ($record) {
            return $record->radnummer;
        }

        return null;
    }

    public function getPin(): ?string
    {
        $phone = $this->sm->getContext()->getCallerPhoneNumber();
        $record = MvgradMockState::where('phone', '=', $phone, 'and')->where('rental_state', '=', 'running')->first();

        if ($record) {
            return $record->pin;
        }

        return null;
    }

    private function generateMockPin()
    {
        return mt_rand(1, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);
    }
}
