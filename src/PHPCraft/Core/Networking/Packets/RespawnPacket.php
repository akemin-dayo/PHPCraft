<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class RespawnPacket {
	const id = 0x09;
	public $world;

	public function __construct($world=0x00) {
		$this->world = $world;
	}

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->world = $StreamWrapper->readByte();
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
			$StreamWrapper->writeByte($this->world);

		return $StreamWrapper->writePacket($str);
	}
}
