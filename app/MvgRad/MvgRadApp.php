<?php

declare(strict_types=1);
namespace AppFree\MvgRad;
class MvgRadApp implements \Finite\StatefulInterface
{
    private string $state;


//    public const STATE

    public function getFiniteState(): string
    {
        return $this->state;
    }

    public function setFiniteState($state): void
    {
        $this->state = $state;
    }
}
