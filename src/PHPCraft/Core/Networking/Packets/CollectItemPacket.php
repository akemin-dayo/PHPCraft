<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class CollectItemPacket {
	const id = 0x16;

	public $collected_eid;
	public $collector_eid;

	public function __construct($collected_eid, $collector_eid) {
		$this->collected_eid = $collected_eid;
		$this->collector_eid = $collector_eid;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$packetToClient = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->collected_eid) .
		$StreamWrapper->writeInt($this->collector_eid);

		return $StreamWrapper->writePacket($packetToClient);
	}
}
