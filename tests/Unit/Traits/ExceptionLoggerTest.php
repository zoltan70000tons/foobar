<?php

namespace Tests\Unit\Traits;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Traits\ExceptionLogger;
use Exception;

class ExceptionLoggerTest extends TestCase
{
    use ExceptionLogger;

    protected $logFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Setting channel for testing
        $this->setLogChannel('testing');
        $this->setLogLevel('error');

        // Setting file for 
        $this->logFile = storage_path('logs/testing.log');

        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
    //Write to the log using a classic try-catch block and logException function
    public function test_log_exception()
    {
        $this->setLogLevel('error');
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        try {
            throw new Exception('Try Catch controled Exception');
        } catch (\Exception $e) {
            $this->logException($e);
        }
        $logs = file_get_contents($this->logFile);
        $this->assertStringContainsString('New Exception: Try Catch controled Exception', $logs);
    
    }

    //Write to the log using the executeWithLogging function
    public function test_execute_with_logging_exception()
    {

        $this->executeWithLogging(function () {
            throw new Exception('Test exception executeWithLogging');
        });
        $logs = file_get_contents($this->logFile);
        $this->assertStringContainsString('New Exception: Test exception executeWithLogging', $logs);
    }

    // Write to the log using the executeWithLogging function and a customized message
    public function test_it_logs_custom_error_message()
    {
        $this->executeWithLogging(function () {
            throw new Exception('Test exception');
        }, 'Custom error message');
        $logs = file_get_contents($this->logFile);
        $this->assertStringContainsString('New Exception: Custom error message', $logs);
    }

    // Run the code that should not log anything (the file should be empty)
    public function test_it_does_not_log_if_severity_is_lower_than_log_level()
    {
        $this->setLogLevel('emergency');
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        $this->executeWithLogging(function () {
            throw new Exception('No Log exception');
        });

        $logs = file_exists($this->logFile) ? file_get_contents($this->logFile) : '';
        $this->assertStringNotContainsString('New Exception: No Log exception', $logs);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
}
