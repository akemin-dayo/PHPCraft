<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class EntityVelocityPacket {
	const id = 0x1c;
	public $entityId;
	public $xVel;
	public $yVel;
	public $zVel;

	public function __construct($entityId, $xVel, $yVel, $zVel) {
		$this->entityId = $entityId;
		$this->xVel = $xVel;
		$this->yVel = $yVel;
		$this->zVel = $zVel;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->entityId) .
		$StreamWrapper->writeShort($this->xVel) .
		$StreamWrapper->writeShort($this->yVel) .
		$StreamWrapper->writeShort($this->zVel);

		return $StreamWrapper->writePacket($str);
	}
}
