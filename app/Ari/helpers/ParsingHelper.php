<?php

declare(strict_types=1);

namespace AppFree\Ari\helpers;

use Exception;

/**
 * phpari - A PHP Class Library for interfacing with Asterisk(R) ARI
 * Copyright (C) 2014  Nir Simionovich
 */

class ParsingHelper
{
    /**
     *
     */
    public function __construct()
    {
        try {
            return false;
        } catch (Exception $e) {
            die("Exception raised: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    /**
     * Ascertain if the input provided by $rawInput is one of the following: JSON_STRING, JSON_OBJECT, ASSOC_ARRAY.
     * The return value shall always be an ASSOC_ARRAY, represting $rawInput in a unified manner
     */
    public function parseRequestData($rawInput = null): array
    {
        try {

            if ($rawInput == null) {
                throw new Exception("Input must be defined", 503);
            }

            $result = [];

            if (is_string($rawInput)) {
                $result = json_decode($rawInput, true);
            }

            if (is_array($rawInput)) {
                $result = $rawInput;
            }

            if (is_object($rawInput)) {
                $result = json_decode(json_encode($rawInput), true);
            }

            return $result;

        } catch (Exception $e) {
            die("Exception raised: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    /**
     * Ascertain if the input is an associative array or not.
     * This test is far from being perfect - still needs sanity checks and better resolution
     *
     * @param $arr
     *
     * @return bool
     */
    public function isAssoc($arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
