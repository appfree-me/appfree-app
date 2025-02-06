<?php

namespace AppFree\appfree\modules\MvgRad\Api\Mock;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;
use AppFree\Models\MvgradMockState;
use Illuminate\Support\Facades\DB;

class MvgRadApi implements MvgRadApiInterface
{
    public const API_ID = "mock";

    public function __construct(private AppFreeStateMachine $sm)
    {
    }

    public function doAusleihe(string $radnummer): ?string
    {
        if ($this->isAusleiheRunning()) {
            return null;
        }

        $pin = $this->generateMockPin();

        DB::table("mvgrad_mock_state")->updateOrInsert(
            [
                'phone' => $this->sm->getContext()->getCallerPhoneNumber()
            ],
            [
                'rental_state' => 'running',
                'pin' => $pin,
                'radnummer' => $radnummer,
            ]
        );

        return $pin;
    }

    public function doRueckgabe()
    {
        DB::table("mvgrad_mock_state")->updateOrInsert(
            [
                'phone' => $this->sm->getContext()->getCallerPhoneNumber(),
            ],
            [
                'rental_state' => 'not-running',
                'pin' => '',
                'radnummer' => ''
            ]);
    }

    public function isAusleiheRunning(): bool
    {
        $phone = $this->sm->getContext()->getCallerPhoneNumber();

        return MvgradMockState::where('phone', '=', $phone, 'and')->where('rental_state', '=', 'running')->count() > 0;
    }

    public function getPin(): ?string
    {
        $phone = $this->sm->getContext()->getCallerPhoneNumber();
        return MvgradMockState::where('phone', '=', $phone, 'and')->where('rental_state', '=', 'running')->first()->pin;
    }

    private function generateMockPin()
    {
        return mt_rand(1, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);
    }
}
