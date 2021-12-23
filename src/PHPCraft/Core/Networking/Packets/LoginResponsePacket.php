<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class LoginResponsePacket {
	const id = 0x01;
	public $Dimension;
	public $EntityID;
	public $Seed;

	public function __construct($entityID, $seed, $dimension) {
		$this->EntityID = $entityID;
		$this->Seed = $seed;
		$this->Dimension = $dimension;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$p = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->EntityID) .
		$StreamWrapper->writeShort(0) .
		$StreamWrapper->writeLong($this->Seed) .
		$StreamWrapper->writeByte($this->Dimension);

		return $StreamWrapper->writePacket($p);
	}
}
