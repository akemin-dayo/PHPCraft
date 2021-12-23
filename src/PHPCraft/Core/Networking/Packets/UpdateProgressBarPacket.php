<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class UpdateProgressBarPacket {
	const id = 0x69;
	public $window_id;
	public $progress_bar;
	public $progress_value;

	public function __construct($window_id, $progress_bar, $progress_value) {
		$this->window_id = $window_id;
		$this->progress_bar = $progress_bar;
		$this->progress_value = $progress_value;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$p = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeByte($this->window_id) .
		$StreamWrapper->writeShort($this->progress_bar) .
		$StreamWrapper->writeShort($this->progress_value);

		return $StreamWrapper->writePacket($p);
	}
}
