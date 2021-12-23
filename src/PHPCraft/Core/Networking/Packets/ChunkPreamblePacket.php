<?php
namespace PHPCraft\Core\Networking\Packets;
use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Networking\StreamWrapper;

class ChunkPreamblePacket {
	const id = 0x32;
	public $x;
	public $z;
	public $load;

	public function __construct($x, $z, $load = true) {
		$this->x = $x;
		$this->z = $z;
		$this->load = $load;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeInt($this->z) .
		$StreamWrapper->writeBool($this->load);

		return $StreamWrapper->writePacket($str);
	}
}
