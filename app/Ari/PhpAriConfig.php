<?php

declare(strict_types=1);

namespace AppFree\Ari;

use Exception;

/**
 * phpari - A PHP Class Library for interfacing with Asterisk(R) ARI
 * Copyright (C) 2014  Nir Simionovich
 */
class PhpAriConfig
{
    private array $config = [];

    /**
     * @throws Exception
     */
    public function __construct(string|array $config = __DIR__ . '/../../phpari.ini')
    {
        if (is_array($config)) {
            $this->config = $config;
            return;
        }

        $ini = parse_ini_file($config, true);
        if (! $ini) {
            throw new Exception("Invald INI file provided: '$config'");
        }

        $this->config = $ini;
    }


    public function __get($section)
    {
        return $this->config[$section];
    }

}
