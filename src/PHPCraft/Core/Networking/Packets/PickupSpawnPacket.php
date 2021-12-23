<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PickupSpawnPacket {
	const id = 0x15;

	public $eid;
	public $item;
	public $itemcount;
	public $damage;
	public $x;
	public $y;
	public $z;
	public $rotation;
	public $pitch;
	public $roll;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->eid = $StreamWrapper->readInt();
		$this->item = $StreamWrapper->readShort();
		$this->itemcount = $StreamWrapper->readByte();
		$this->damage = $StreamWrapper->readShort();
		$this->x = $StreamWrapper->readInt();
		$this->y = $StreamWrapper->readInt();
		$this->z = $StreamWrapper->readInt();
		$this->rotation = $StreamWrapper->readByte();
		$this->pitch = $StreamWrapper->readByte();
		$this->roll = $StreamWrapper->readByte();
	}
}
