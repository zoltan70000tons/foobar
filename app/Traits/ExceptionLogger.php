<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait ExceptionLogger
{
    protected $logChannel = 'daily';
    protected $logLevel;

    public function setLogChannel(string $channel)
    {
        $this->logChannel = $channel;
    }

    public function setLogLevel(string $level)
    {
        $this->logLevel = $level;
    }

    protected function logException(Exception $exception)
    {
        $logLevel = $this->logLevel ?? config('settings.log_level', 'error');

        if ($this->shouldLog($logLevel, $this->getSeverity())) {
            $file = $exception->getFile();
            $line = $exception->getLine();
            $function = $exception->getTrace()[0]['function'] ?? 'N/A';
            $class = $exception->getTrace()[0]['class'] ?? 'N/A';
            $exceptionType = get_class($exception);
            $message = $exception->getMessage();

            $formattedMessage = "\n\tNew Exception: $message\n" .
                "\tType: $exceptionType\n" .
                "\tFile: $file\n" .
                "\tLine: $line\n" .
                "\tFunction: $function\n" .
                "\tClass: $class";

            Log::channel($this->logChannel)->log($this->getSeverity(), $formattedMessage);
        }
    }

    private function shouldLog($logLevel, $severity)
    {
        $levels = [
            'debug' => 0,
            'info' => 1,
            'notice' => 2,
            'warning' => 3,
            'error' => 4,
            'critical' => 5,
            'alert' => 6,
            'emergency' => 7,
        ];

        return $levels[$severity] >= $levels[$logLevel];
    }

    protected function getSeverity(): string
    {
        return property_exists($this, 'severity') ? $this->severity : 'error';
    }

    public function executeWithLogging(callable $callback, string $customErrorMessage = null)
    {
        try {
            return $callback();
        } catch (Exception $exception) {
            if ($customErrorMessage) {
                $this->logException(new Exception($customErrorMessage, 0, $exception));
            } else {
                $this->logException($exception);
            }
            return null;
        }
    }
}
