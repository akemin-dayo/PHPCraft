<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class SetPlayerPositionPacket {
	const id = 0x0D;
	public $x;
	public $y;
	public $z;
	public $stance;
	public $yaw;
	public $pitch;
	public $onGround;

	public function __construct($x, $y, $z, $stance, $yaw, $pitch, $onGround) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->stance = $stance;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->onGround = $onGround;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeDouble($this->x) .
		$StreamWrapper->writeDouble($this->stance) .
		$StreamWrapper->writeDouble($this->y) .
		$StreamWrapper->writeDouble($this->z) .
		$StreamWrapper->writeFloat($this->yaw) .
		$StreamWrapper->writeFloat($this->pitch) .
		$StreamWrapper->writeBool($this->onGround);

		return $StreamWrapper->writePacket($str);
	}
}
