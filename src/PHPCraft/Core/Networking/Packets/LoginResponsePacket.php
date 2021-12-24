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
		$p = $StreamWrapper->writeInt8(self::id) .
		$StreamWrapper->writeInt($this->EntityID) .
		$StreamWrapper->writeInt16(0) .
		$StreamWrapper->writeLong($this->Seed) .
		$StreamWrapper->writeInt8($this->Dimension);

		return $StreamWrapper->writePacket($p);
	}
}
