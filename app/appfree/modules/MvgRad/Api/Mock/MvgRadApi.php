<?php

namespace AppFree\appfree\modules\MvgRad\Api\Mock;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\StateMachineContext;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Support\Facades\DB;

class MvgRadApi implements MvgRadApiInterface
{

    public const API_ID = "mock";

    public function __construct(private StateMachineInterface $sm)
    {
    }

    public function doAusleihe(string $radnummer): ?string
    {

        if ($this->isAusleiheRunning()) {
            return null;
        }

        $pin = $this->generateMockPin();

        DB::table("mvgrad_mock_state")->updateOrInsert(['phone' => $ctx->getCallerPhoneNumber()],[
            'phone' => $this->sm->getContext()->getCallerPhoneNumber(),
            'rental_state' => 'running',
            'pin' => $pin,
            'radnummer' => $radnummer,
        ]);




    }

    public function isAusleiheRunning(): bool
    {
        return true;
    }

    public function getPin(): ?string
    {
        return "999";
    }

    private function generateMockPin()
    {
        return mt_rand(1,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
    }
}
