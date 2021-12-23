<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class PlayerGroundedPacket {
	const id = 0x0A;
	public $onGround;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->onGround = $StreamWrapper->readBool();
	}
}
