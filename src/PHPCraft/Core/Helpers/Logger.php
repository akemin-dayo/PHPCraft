<?php

namespace PHPCraft\Core\Helpers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MLogger;

class Logger {
	const COLOUR_CYANBOLD = "\033[36;1m";
	const COLOUR_REDBOLD = "\033[31;1m";
	const COLOUR_GREENBOLD = "\033[32;1m";
	const COLOUR_YELLOWBOLD = "\033[33;1m";
	const COLOUR_BOLD = "\033[1m";
	const COLOUR_RESET = "\033[0m";

	const PREFIX = "[PHPCraft] ";
	const ERROR_PREFIX = "[ERROR] ";
	const WARNING_PREFIX = "[WARNING] ";
	const INFO_PREFIX = "[INFO] ";
	const LOG_PREFIX = "[LOG] ";

	public $options;
	public $PacketLog;

	public function __construct() {
		if (!file_exists("logs/")) {
			mkdir("logs/");
		}

		$this->PacketLog = new MLogger('PacketLogger');
		$this->PacketLog->pushHandler(new StreamHandler('logs/packet_log'), MLogger::INFO);
		$this->ServerLog = new MLogger('ServerLogger');
#		$this->OutLog = new MLogger('OutLogger');
#		$this->OutLog->pushHandler(new StreamHandler('logs/out_log'), MLogger::INFO);
	}

	public function throwLog($msg) {
		$response = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_GREENBOLD . $this::INFO_PREFIX . $this::COLOUR_RESET . $msg . PHP_EOL;
		$this->ServerLog->addInfo($response);
#		$this->OutLog->addInfo($response);
	}

	public function throwWarning($msg) {
		$response = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_YELLOWBOLD . $this::WARNING_PREFIX . $this::COLOUR_RESET . $msg . PHP_EOL;
		$this->ServerLog->addWarning($response);
	}

	public function throwError($msg) {
		$response = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_REDBOLD . $this::ERROR_PREFIX . $this::COLOUR_RESET . $msg . PHP_EOL;
		$this->ServerLog->addError($response);
	}

	public function logPacket($packet) {
		$this->PacketLog->addInfo($packet);
	}
}
