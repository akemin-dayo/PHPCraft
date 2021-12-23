<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class EntityTeleportPacket {
	const id = 0x22;
	public $entityId;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $pitch;

	public function __construct($entityId, $x, $y, $z, $yaw, $pitch) {
		$this->entityId = $entityId;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->entityId) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeInt($this->y) .
		$StreamWrapper->writeInt($this->z) .
		$StreamWrapper->writeByte($this->yaw) .
		$StreamWrapper->writeByte($this->pitch);

		return $StreamWrapper->writePacket($str);
	}
}
