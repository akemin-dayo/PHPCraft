<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PlayerPositionPacket {
	const id = 0x0B;

	public $x;
	public $y;
	public $stance;
	public $z;
	public $onGround;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->x = $StreamWrapper->readDouble();
		$this->y = $StreamWrapper->readDouble();
		$this->stance = $StreamWrapper->readDouble();
		$this->z = $StreamWrapper->readDouble();
		$this->onGround = $StreamWrapper->readBool();
	}
}
