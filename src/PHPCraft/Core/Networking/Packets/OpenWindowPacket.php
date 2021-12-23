<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class OpenWindowPacket {
	const id = 0x64;
	public $windowId;
	public $inventoryType;
	public $windowTitle;
	public $numberOfSlots;

	public function __construct($windowId, $inventoryType, $windowTitle, $numberOfSlots) {
		$this->windowId = $windowId;
		$this->inventoryType = $inventoryType;
		$this->windowTitle = $windowTitle;
		$this->numberOfSlots = $numberOfSlots;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
			$StreamWrapper->writeByte($this->windowId) .
			$StreamWrapper->writeByte($this->inventoryType) .
			// TODO (Karen): Implement string8 support and use it here.
			$StreamWrapper->writeString16WithoutStringLengthShort($this->windowTitle) .
			$StreamWrapper->writeByte($this->numberOfSlots);

		return $StreamWrapper->writePacket($str);
	}
}
