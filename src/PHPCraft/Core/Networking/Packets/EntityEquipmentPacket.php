<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class EntityEquipmentPacket {
	const id = 0x05;

	public $eid;
	public $slot;
	public $itemid;
	public $damage;

	public function __construct($eid, $slot, $itemid, $damage) {
		$this->eid = $eid;
		$this->slot = $slot;
		$this->itemid = $itemid;
		$this->damage = $damage;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
			$StreamWrapper->writeInt($this->eid) .
			$StreamWrapper->writeShort($this->slot) .
			$StreamWrapper->writeShort($this->itemid) .
			$StreamWrapper->writeShort($this->damage);

		return $StreamWrapper->writePacket($str);
	}
}
