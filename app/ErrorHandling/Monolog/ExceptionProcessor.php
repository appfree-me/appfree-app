<?php

declare(strict_types=1);

namespace AppFree\ErrorHandling\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class ExceptionProcessor implements ProcessorInterface
{
    public function __invoke(array|LogRecord $record)
    {
        $output = [];

        foreach ($record as $key => $value) {
            if (is_array($value)) {
                $output[$key] = $this->__invoke($value);
            } elseif ($value instanceof \Throwable) {
                $output[$key] = [
                    'message' => $value->getMessage(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'code' => $value->getCode(),
                ];
            } else {
                $output[$key] = $value;
            }
        }

        return $output;
    }
}
