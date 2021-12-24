<?php
namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class ChatMessagePacket {
	const id = 0x03;
	public $message;

	public function __construct($message="") {
		$this->message = $message;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeString16WithStringLengthPrefix($this->message);

		return $StreamWrapper->writePacket($str);
	}

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->message = $StreamWrapper->readString16();
	}
}
