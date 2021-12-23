<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class HoldingChangePacket {
	const id = 0x10;
	public $slotid;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->slotid = $StreamWrapper->readShort();
	}
}
