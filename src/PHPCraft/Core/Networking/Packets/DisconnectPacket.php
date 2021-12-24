<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class DisconnectPacket {
	const id = 0xFF;
	public $reason;

	public function __construct($reason="") {
		$this->reason = $reason;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeString16WithStringLengthPrefix($this->reason);

		return $StreamWrapper->writePacket($str);
	}

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->reason = $StreamWrapper->readString16();
	}
}
