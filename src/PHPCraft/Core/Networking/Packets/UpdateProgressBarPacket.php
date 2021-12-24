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
		$p = $StreamWrapper->writeInt8(self::id) .
		$StreamWrapper->writeInt8($this->window_id) .
		$StreamWrapper->writeInt16($this->progress_bar) .
		$StreamWrapper->writeInt16($this->progress_value);

		return $StreamWrapper->writePacket($p);
	}
}
