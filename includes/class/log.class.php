<?php
/**
 * Log
 *
 * Backward-compatible facade for the modern KcbLogger engine.
 * Preserves legacy write() calls while routing all logging through PSR-3
 * daily rotating log files and atomic file operations.
 */

require_once __DIR__ . '/kcbLogger.class.php';

class Log
{
    /** @var KcbLogger */
    private $logger;

    public function __construct()
    {
        $this->logger = KcbLogger::getInstance();
    }

    /**
     * Backward-compatible write method
     *
     * @param string $message
     */
    public function write($message)
    {
        $this->logger->write($message);
    }

    /**
     * PSR-3 convenience delegation methods
     */
    public function debug($message, array $context = [])    { $this->logger->debug($message, $context); }
    public function info($message, array $context = [])     { $this->logger->info($message, $context); }
    public function notice($message, array $context = [])   { $this->logger->notice($message, $context); }
    public function warning($message, array $context = [])  { $this->logger->warning($message, $context); }
    public function error($message, array $context = [])    { $this->logger->error($message, $context); }
    public function critical($message, array $context = []) { $this->logger->critical($message, $context); }
    public function alert($message, array $context = [])    { $this->logger->alert($message, $context); }
    public function emergency($message, array $context = []) { $this->logger->emergency($message, $context); }

    /**
     * Backward-compatible helper retained for legacy callers
     */
    private function getServerVars()
    {
        return '';
    }
}
