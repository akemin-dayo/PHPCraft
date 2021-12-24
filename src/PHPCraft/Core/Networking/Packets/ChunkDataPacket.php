<?php

namespace PHPCraft\Core\Networking\Packets;

use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Networking\StreamWrapper;
use PHPCraft\Core\World\Chunk;

class ChunkDataPacket {
	const id = 0x33;

	public $x;
	public $y;
	public $z;

	public $Width;
	public $Height;
	public $Depth;
	public $BlockData;

	public function __construct($x, $y, $z, $Width, $Height, $Depth, $BlockData) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->Width = $Width - 1;
		$this->Height = $Height - 1;
		$this->Depth = $Depth - 1;
		$this->BlockData = $BlockData;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeInt8(self::id) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeInt16($this->y) .
		$StreamWrapper->writeInt($this->z) .
		$StreamWrapper->writeInt8(15) .
		$StreamWrapper->writeInt8(127) .
		$StreamWrapper->writeInt8(15) .
		$StreamWrapper->writeInt(strlen($this->BlockData)) .
		$this->BlockData;

		return $StreamWrapper->writePacket($str);
	}
}
