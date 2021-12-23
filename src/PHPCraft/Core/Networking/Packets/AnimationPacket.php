<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class AnimationPacket {
	const id = 0x12;

	public $eid;
	public $animate;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->eid = $StreamWrapper->readInt();
		$this->animate = $StreamWrapper->readByte();
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$packetToClient = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->eid) .
		$StreamWrapper->writeByte($this->animate);

		return $StreamWrapper->writePacket($packetToClient);
	}
}
