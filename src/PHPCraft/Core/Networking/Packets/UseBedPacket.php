<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class UseBedPacket {
	const id = 0x11;

	public $eid;
	public $in_bed;
	public $x;
	public $y;
	public $z;

	public function writePacket(StreamWrapper $StreamWrapper) {
		$packetToClient = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->eid) .
		$StreamWrapper->writeInt8($this->in_bed) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeInt8($this->y) .
		$StreamWrapper->writeInt($this->z);

		return $StreamWrapper->writePacket($packetToClient);
	}
}
