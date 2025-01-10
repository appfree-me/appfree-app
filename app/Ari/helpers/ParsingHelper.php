<?php
declare(strict_types=1);

namespace AppFree\Ari\helpers;
    use Exception;

    /**
     * phpari - A PHP Class Library for interfacing with Asterisk(R) ARI
     * Copyright (C) 2014  Nir Simionovich
     */

class ParsingHelper {

    /**
     *
     */
    function __construct()
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
    function parseRequestData($rawInput = NULL): array
    {
        try {

            if ($rawInput == NULL)
                throw new Exception ("Input must be defined", 503);

            $result = array();

            if (is_string($rawInput))
                $result = json_decode($rawInput, TRUE);

            if (is_array($rawInput))
                $result = $rawInput;

            if (is_object($rawInput))
                $result = json_decode(json_encode($rawInput), TRUE);

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
    function isAssoc($arr) : bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
