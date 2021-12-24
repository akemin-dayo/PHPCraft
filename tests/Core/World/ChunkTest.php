<?php

use PHPCraft\Core\World\Chunk;
use PHPCraft\API\Coordinates3D;
use PHPCraft\API\Coordinates2D;
use PHPUnit\Framework\TestCase;

class ChunkTest extends TestCase {
	public function testChunkGeneration() {
		$chunkCoordinates = new Coordinates2D(0,0);
		$blockCoordinates = new Coordinates3D(10,10,10);
		$chunk = new Chunk($chunkCoordinates);

		$this->assertEquals($chunk->getBlockID($blockCoordinates), 0x00, "Expects block to be air in the chunk");
	}
}

