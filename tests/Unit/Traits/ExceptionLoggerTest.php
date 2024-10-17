<?php

use Illuminate\Support\Facades\Log;

uses(\App\Traits\ExceptionLogger::class);

beforeEach(function () {
    // Setting channel for testing
    $this->setLogChannel('testing');
    $this->setLogLevel('error');

    // Setting file for 
    $this->logFile = storage_path('logs/testing.log');

    if (file_exists($this->logFile)) {
        unlink($this->logFile);
    }
});

test('log exception', function () {
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
});

test('execute with logging exception', function () {
    $this->executeWithLogging(function () {
        throw new Exception('Test exception executeWithLogging');
    });
    $logs = file_get_contents($this->logFile);
    $this->assertStringContainsString('New Exception: Test exception executeWithLogging', $logs);
});

test('it logs custom error message', function () {
    $this->executeWithLogging(function () {
        throw new Exception('Test exception');
    }, 'Custom error message');
    $logs = file_get_contents($this->logFile);
    $this->assertStringContainsString('New Exception: Custom error message', $logs);
});

test('it does not log if severity is lower than log level', function () {
    $this->setLogLevel('emergency');
    if (file_exists($this->logFile)) {
        unlink($this->logFile);
    }

    $this->executeWithLogging(function () {
        throw new Exception('No Log exception');
    });

    $logs = file_exists($this->logFile) ? file_get_contents($this->logFile) : '';
    $this->assertStringNotContainsString('New Exception: No Log exception', $logs);
});

afterEach(function () {
    if (file_exists($this->logFile)) {
        unlink($this->logFile);
    }
});