<?php
namespace Jack\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger {

	public static $logsDir;

	public function log($level, $message, array $context = array()) {
		error_log(sprintf("%s - %s:%d - %s -- %s\r\n", strtoupper($level), $context[0], $context[1], $context[2], var_export($message, TRUE)), 3, static::getLogFilePath($level));
	}

	protected static function getLogFilePath($level) {
		if (empty(static::$logsDir)) throw new \BadMethodCallException("Logs directory path is not set");
		return static::$logsDir."/$level.log";
	}

}
