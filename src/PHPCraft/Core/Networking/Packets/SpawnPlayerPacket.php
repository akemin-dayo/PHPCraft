<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class SpawnPlayerPacket {
	const id = 0x14;
	public $entityId;
	public $playerName;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $pitch;
	public $currentItem;

	public function __construct($entityId, $playerName, $x, $y, $z, $yaw, $pitch, $currentItem) {
		$this->entityId = $entityId;
		$this->playerName = $playerName;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->currentItem = $currentItem;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->entityId) .
		$StreamWrapper->writeShort(strlen($this->playerName)) .
		$StreamWrapper->writeString16WithoutStringLengthShort($this->playerName) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeInt($this->y) .
		$StreamWrapper->writeInt($this->z) .
		$StreamWrapper->writeByte($this->yaw) .
		$StreamWrapper->writeByte($this->pitch) .
		$StreamWrapper->writeShort($this->currentItem);

		return $StreamWrapper->writePacket($str);
	}
}
