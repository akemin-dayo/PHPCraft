<?php
namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class EntityRelativeMovePacket {
	const id = 0x1F;

	public $eid; // Entity ID
	public $dX; // X axis relative movement as absolute int
	public $dY; // Y axis relative movement as absolute int
	public $dZ; // Z axis relative movement as absolute int

	public function __construct($eid, $dX, $dY, $dZ) {
		$this->eid = $eid;
		$this->dX = $dX;
		$this->dY = $dY;
		$this->dZ = $dZ;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
			$StreamWrapper->writeInt($this->eid) .
			$StreamWrapper->writeByte($this->dX) .
			$StreamWrapper->writeByte($this->dY) .
			$StreamWrapper->writeByte($this->dZ);

		return $StreamWrapper->writePacket($str);
	}
}
