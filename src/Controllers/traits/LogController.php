<?php

namespace Controllers\traits;

use Exception;

trait LogController
{
    /**
     * @param string $file
     * @param string $class
     * @param string $method
     * @param string $line
     * @param Exception $e
     *
     * @return string
     */
    protected function prepareLogInfoWithException(
        string $file,
        string $class,
        string $method,
        string $line,
        Exception $e
    ): string
    {
        return json_encode([
            "exception_location" => [
                "file" => $file,
                "class" => $class,
                "method" => $method,
                "line" => $line,
            ],
            "exception_info" => [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ],
        ]);
    }

}
