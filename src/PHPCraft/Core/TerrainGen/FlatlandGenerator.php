<?php
/**
 * FlatlandGenerator just generates chunks that are exactly the same.
 * Dirt from layers 1-8, and grass on layer 9.
 */
namespace PHPCraft\Core\TerrainGen;

use PHPCraft\API\Coordinates3D;
use PHPCraft\Core\World\Chunk;

class FlatlandGenerator {
	const LevelType = "FLAT";
	public $spawnpoint;
	public $layers = [];

	public function __construct() {
		$this->spawnpoint = new Coordinates3D(0, 10, 0);
	}

	public function generateChunk($Coordinates2DPos) {
		$newC = new Chunk($Coordinates2DPos);

		$y = 0;
		// Flatland, dirt from layer 1 - 8, grass on layer 9.
		while ($y < 10) {
			for ($x = 0; $x < 16; $x++) {
				for ($z = 0; $z < 16; $z++) {
					if ($y < 9) {
						$newC->setBlockID(new Coordinates3D($x, $y, $z), 0x03);
					} else {
						$newC->setBlockID(new Coordinates3D($x, $y, $z), 0x02);
					}
				}
			}

			$y++;
		}

		return $newC;
	}
}
