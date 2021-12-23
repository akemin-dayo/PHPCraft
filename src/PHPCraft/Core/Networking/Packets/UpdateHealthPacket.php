<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class UpdateHealthPacket {
	const id = 0x08;
	public $health;

	public function __construct($health=0) {
		$this->health = $health;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$p = $StreamWrapper->writeByte(self::id) .
			$StreamWrapper->writeShort($this->health);

		return $StreamWrapper->writePacket($p);
	}
}
