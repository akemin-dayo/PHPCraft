<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class HandshakeResponsePacket {
	const id = 0x02;
	public $connectionHash;

	public function __construct($connectionHash = "-") {
		$this->connectionHash = $connectionHash;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeShort(strlen($this->connectionHash)) .
		$StreamWrapper->writeString16WithoutStringLengthShort($this->connectionHash);

		return $StreamWrapper->writePacket($str);
	}
}
