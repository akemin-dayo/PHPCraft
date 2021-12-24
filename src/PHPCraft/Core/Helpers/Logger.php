<?php
namespace PHPCraft\Core\Helpers;

use Monolog\Logger as MonologLogger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger {
	const COLOUR_CYANBOLD = "\033[36;1m";
	const COLOUR_REDBOLD = "\033[31;1m";
	const COLOUR_GREENBOLD = "\033[32;1m";
	const COLOUR_YELLOWBOLD = "\033[33;1m";
	const COLOUR_MAGENTABOLD = "\033[35;1m";
	const COLOUR_BOLD = "\033[1m";
	const COLOUR_RESET = "\033[0m";

	const PREFIX = "[PHPCraft] ";
	const DEBUG_PREFIX = "[DEBUG] ";
	const INFO_PREFIX = "[INFO] ";
	const WARNING_PREFIX = "[WARNING] ";
	const ERROR_PREFIX = "[ERROR] ";

	private $ServerLogger;
	private $PacketLogger;

	public function __construct() {
		if (!file_exists("logs/")) {
			mkdir("logs/");
		}

		// [%channel%] and [%level_name%] are rendered redundant by our custom colourised formatting below.
		$dateFormat = "Y-m-d H:i:s";
		$messageFormat = "[%datetime%] %message% %context% %extra%\n";
		$logFormatter = new LineFormatter($messageFormat, $dateFormat, true, true);

		$stdoutHandler = new StreamHandler(STDOUT);
		$stdoutHandler->setFormatter($logFormatter);

		$serverLogFileHandler = new StreamHandler('logs/server.log');
		$serverLogFileHandler->setFormatter($logFormatter);
		
		$packetLogFileHandler = new StreamHandler('logs/packets.log');
		$packetLogFileHandler->setFormatter($logFormatter);

		$this->ServerLogger = new MonologLogger('PHPCraftServerLogger');
		$this->ServerLogger->pushHandler($stdoutHandler);
		$this->ServerLogger->pushHandler($serverLogFileHandler);

		$this->PacketLogger = new MonologLogger('PHPCraftPacketLogger');
		$this->PacketLogger->pushHandler($stdoutHandler);
		$this->PacketLogger->pushHandler($packetLogFileHandler);
	}

	public function logDebug($stringToLog) {
		$finalStringToLog = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_MAGENTABOLD . $this::DEBUG_PREFIX . $this::COLOUR_RESET . $stringToLog;
		$finalStringToLog = rtrim($finalStringToLog);
		$this->ServerLogger->debug($finalStringToLog);
	}

	public function logInfo($stringToLog) {
		$finalStringToLog = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_GREENBOLD . $this::INFO_PREFIX . $this::COLOUR_RESET . $stringToLog;
		$finalStringToLog = rtrim($finalStringToLog);
		$this->ServerLogger->info($finalStringToLog);
	}

	public function logWarning($stringToLog) {
		$finalStringToLog = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_YELLOWBOLD . $this::WARNING_PREFIX . $this::COLOUR_RESET . $stringToLog;
		$finalStringToLog = rtrim($finalStringToLog);
		$this->ServerLogger->warning($finalStringToLog);
	}

	public function logError($stringToLog) {
		$finalStringToLog = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_REDBOLD . $this::ERROR_PREFIX . $this::COLOUR_RESET . $stringToLog;
		$finalStringToLog = rtrim($finalStringToLog);
		$this->ServerLogger->error($finalStringToLog);
	}

	public function logPacket($stringToLog) {
		$finalStringToLog = $this::COLOUR_CYANBOLD . $this::PREFIX . $this::COLOUR_MAGENTABOLD . $this::DEBUG_PREFIX . $this::COLOUR_RESET . $stringToLog;
		$finalStringToLog = rtrim($finalStringToLog);
		$this->PacketLogger->debug($finalStringToLog);
	}
}
