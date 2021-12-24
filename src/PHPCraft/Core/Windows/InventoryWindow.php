<?php

namespace PHPCraft\Core\Windows;

class InventoryWindow extends Window {
	const name = "Inventory";
	const craftingOutputIndex = 0;
	const hotbarIndex = 36;
	const craftingGridIndex = 1;
	const armorIndex = 5;
	const mainIndex = 9;

	// InventoryWindow does not have window type.
	const type = -1;

	public function __construct($CraftingRepository) {
		parent::__construct();

		$this->WindowAreas = [
			new CraftingWindowArea($CraftingRepository, self::craftingOutputIndex),
			new ArmorWindowArea(self::armorIndex),
			new WindowArea(self::mainIndex, 27, 9, 3),
			new WindowArea(self::hotbarIndex, 9, 9, 1)
		];
	}

	public function getLinkedArea($index, $slot) {
		if ($index == 0 || $index == 1 || $index == 3) {
			return $this->WindowAreas[2];
		}
		else {
			return $this->WindowAreas[3];
		}
	}

	// TODO (vy)
	public function pickUpStack() {

	}

	// TODO (vy)
	public function copyToInventory() {

	}

	public function findEmptySpace() {
		// Go through window area and find an empty slot
		$slot_index = -1;

		$window_areas = [
			$this->WindowAreas[3],
			$this->WindowAreas[2]
		];

		foreach ($window_areas as $Area) {
			for ($i = 0; $i < $Area->length; $i++) {
				if ($slot_index > -1) {
					break;
				}

				$index = $Area->startIndex + $i;
				$item = $Area->Items[$i];

				if ($item->isEmpty()) {
					$slot_index = $index;
					break;
				} else if ($item->icount >= 64) {
					continue;
				}
			}
		}

		return $slot_index;
	}
}
