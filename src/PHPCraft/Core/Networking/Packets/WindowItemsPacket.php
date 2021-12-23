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
		$str = $StreamWrapper->writeInt8(self::id) .
		$StreamWrapper->writeInt8($this->windowId) .
		$StreamWrapper->writeInt16(count($this->items));

		for ($i = 0; $i < count($this->items); $i++) {
			$str = $str . $StreamWrapper->writeInt16($this->items[$i]->id);

			if (!$this->items[$i]->isEmpty()) {
				$str = $str . $StreamWrapper->writeInt8($this->items[$i]->icount) .
				$StreamWrapper->writeInt16($this->items[$i]->metadata);
			}
		}

		return $StreamWrapper->writePacket($str);
	}
}
