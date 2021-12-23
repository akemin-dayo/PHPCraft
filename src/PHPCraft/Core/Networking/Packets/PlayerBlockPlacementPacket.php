<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PlayerBlockPlacementPacket {
	const id = 0x0F;

	public $x;
	public $y;
	public $z;
	public $direction;
	public $blockid;
	public $amount;
	public $damage;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->x = $StreamWrapper->readInt();
		$this->y = $StreamWrapper->readByte();
		$this->z = $StreamWrapper->readInt();
		$this->direction = $StreamWrapper->readByte();
		$this->blockid = $StreamWrapper->readShort();

		if ($this->blockid >= 0x00) {
			$this->amount = $StreamWrapper->readByte();
			$this->damage = $StreamWrapper->readShort();
		}
	}

}
