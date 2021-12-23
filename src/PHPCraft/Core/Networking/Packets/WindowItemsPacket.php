<?php
namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Networking\StreamWrapper;

class WindowItemsPacket {
	const id = 0x68;
	public $windowId;
	public $items;

	public function __construct($windowId, $items) {
		$this->windowId = $windowId;
		$this->items = $items;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeByte($this->windowId) .
		$StreamWrapper->writeShort(count($this->items));

		for ($i = 0; $i < count($this->items); $i++) {
			$str = $str . $StreamWrapper->writeShort($this->items[$i]->id);

			if (!$this->items[$i]->isEmpty()) {
				$str = $str . $StreamWrapper->writeByte($this->items[$i]->icount) .
				$StreamWrapper->writeShort($this->items[$i]->metadata);
			}
		}

		return $StreamWrapper->writePacket($str);
	}
}
