<?php

/**
 * SSResqueLogger
 *
 */
class SSResqueLogger extends Resque_Log {

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed   $level    PSR-3 log level constant, or equivalent string
	 * @param string  $message  Message to log, may contain a { placeholder }
	 * @param array   $context  Variables to replace { placeholder }
	 * @return null
	 */
	public function log($level, $message, array $context = array()) {
        if ($this->verbose || !($level === Psr\Log\LogLevel::INFO || $level === Psr\Log\LogLevel::DEBUG)) {
            fwrite(
                STDOUT,
                '[' . $level . '] [' . strftime('%T %Y-%m-%d') . '] ' . $this->interpolate($message, $context) . PHP_EOL
            );
        }

		// if we have a stack context which is the Exception that was thrown,
		// send that to SS_Log so writers can use that for reporting the error.
		if (!empty($context['stack'])) {
			SS_Log::log($context['stack'], $this->convertLevel($level));
		}
	}

	/**
	 *
	 * @param string $resqueError
	 */
	protected function convertLevel($resqueError) {
		switch($resqueError) {
			case 'emergency':
			case 'alert':
			case 'critical':
			case 'error':
				return SS_Log::ERR;
				break;
			case 'warning':
				return SS_Log::WARN;
				break;
			case 'notice':
			case 'info':
			case 'debug':
				return SS_Log::NOTICE;
				break;
			default:
				return SS_Log::NOTICE;
				break;
		}
	}
}
