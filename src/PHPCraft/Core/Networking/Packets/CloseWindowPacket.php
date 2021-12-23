<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class CloseWindowPacket {
	const id = 0x65;
	public $windowId;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->windowId = $StreamWrapper->readInt8();
	}
}
