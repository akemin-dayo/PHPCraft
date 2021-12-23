<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class AnimationPacket {
	const id = 0x12;

	public $eid;
	public $animate;

	public function readPacket(StreamWrapper $StreamWrapper) {
		$this->eid = $StreamWrapper->readInt();
		$this->animate = $StreamWrapper->readInt8();
	}

}
