<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PlayerDiggingPacket {
	const id = 0x0E;

	// Status can be one of four values:
	// 0: Started digging
	// 2: Finished digging
	// 4: Drop item

	public $status;
	public $x;
	public $y;
	public $z;
	public $face;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->status = $StreamWrapper->readByte();
		$this->x = $StreamWrapper->readInt();
		$this->y = $StreamWrapper->readByte();
		$this->z = $StreamWrapper->readInt();
		$this->face = $StreamWrapper->readByte();
	}
}
