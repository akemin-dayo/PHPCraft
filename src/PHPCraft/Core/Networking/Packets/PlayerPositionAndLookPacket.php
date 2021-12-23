<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PlayerPositionAndLookPacket {
	const id = 0x0D;

	public $x;
	public $y;
	public $stance;
	public $z;
	public $yaw;
	public $pitch;
	public $onGround;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->x = $StreamWrapper->readDouble();
		$this->y = $StreamWrapper->readDouble();
		$this->stance = $StreamWrapper->readDouble();
		$this->z = $StreamWrapper->readDouble();
		$this->yaw = $StreamWrapper->readFloat();
		$this->pitch = $StreamWrapper->readFloat();
		$this->onGround = $StreamWrapper->readBool();
	}
}
