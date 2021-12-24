<?php

namespace PHPCraft\Core\Windows;

use Evenement\EventEmitter;
use PHPCraft\API\ItemStack;

class WindowArea {
	public $Event;
	public $Items = [];
	public $startIndex;
	public $length;
	public $width;
	public $height;

	public function __construct($startIndex, $length, $width, $height) {
		$this->Event = new EventEmitter();
		$this->startIndex = $startIndex;
		$this->length = $length;
		$this->width = $width;
		$this->height = $height;

		$this->setItems($length);
	}

	public function setItems($length) {
		$this->Items = array_fill(0, $length, 0);

		for ($i = 0; $i < $length; $i++) {
			$this->Items[$i] = ItemStack::emptyStack();
		}
	}

	public function moveOrMergeItem($index, $item, $from) {
		$emptyIndex = -1;

		// TODO (vy): Should grab the item's const max stack size.
		$maxStack = 64;

		for ($i = 0; $i < $this->length; $i++) {
			if ($this->Items[$i]->isEmpty() && $emptyIndex == -1) {
				$emptyIndex == $i;
			} else if ($this->Items[$i]->id == $item->id &&
			$this->Items[$i]->metadata == $item->metadata &&
			$this->Items[$i]->icount < $maxStack
			) {
				$emptyIndex = -1;

				if ($from != null) {
					$from->Items[$index] = ItemStack::emptyStack();
				}

				// If mergine two stacks becomes more than max stack, we create one with max size
				// and create one with the remainder.

				if ($this->Items[$i]->icount + $item->icount > $maxStack) {
					$item = new ItemStack($item->id,
					($this->icount - ($maxStack - $this->Items[$i]->icount)),
					$this->metadata,
					$this->nbt);

					$this->Items[$i] = new ItemStack($item->id, $maxStack);
				}

				$this->Items[$i] = new ItemStack($item->id, ($this->Items[$i]->icount + $item->icount), $item->metadata);

				return $i;
			}
		}
	}

	public function copyTo($Area) {
		for ($i = 0; $i < $Area->length; $i++) {
			$Area->Items[$i] = $this->Items[$i];
		}
	}

	public function windowChange() {
		$this->Event->emit("WindowChange", (func_get_args()));
	}

	public function isValid($slot, $index) {
		// TODO (vy): Actually check if the slot is valid?
		return true;
	}
}
