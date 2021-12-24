<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class AttachEntityPacket {
	const id = 0x27;
	public $entity_id;
	public $vehicle_id;

	public function __construct($entity_id, $vehicle_id) {
		$this->entity_id = $entity_id;
		$this->vehicle_id = $vehicle_id;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$p = $StreamWrapper->writeInt8(self::id) .
		$StreamWrapper->writeInt($this->entity_id) .
		$StreamWrapper->writeInt($this->vehicle_id);

		return $StreamWrapper->writePacket($p);
	}
}
