<?php
namespace PHPCraft\Core\Networking\Packets;
use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Networking\StreamWrapper;

class BlockChangePacket {
	const id = 0x35;
	public $x;
	public $y;
	public $z;
	public $blockId;
	public $blockMetadata;

	public function __construct($x, $y, $z, $blockId, $blockMetadata) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->blockId = $blockId;
		$this->blockMetadata = $blockMetadata;
	}

	public function writePacket(StreamWrapper $StreamWrapper) {
		$str = $StreamWrapper->writeByte(self::id) .
		$StreamWrapper->writeInt($this->x) .
		$StreamWrapper->writeByte($this->y) .
		$StreamWrapper->writeInt($this->z) .
		$StreamWrapper->writeByte($this->blockId) .
		$StreamWrapper->writeByte($this->blockMetadata);

		return $StreamWrapper->writePacket($str);
	}
}
