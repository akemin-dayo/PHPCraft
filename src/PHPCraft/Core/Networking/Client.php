<?php

namespace PHPCraft\Core\Networking;

use PHPCraft\API\ItemStack;
use PHPCraft\API\Coordinates2D;
use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Networking\Packets\ChatMessagePacket;
use PHPCraft\Core\Networking\Packets\ChunkDataPacket;
use PHPCraft\Core\Networking\Packets\ChunkPreamblePacket;
use PHPCraft\Core\Networking\Packets\DisconnectPacket;
use PHPCraft\Core\Windows\InventoryWindow;

class Client {
	public $Server;
	public $World;
	public $uuid;
	public $connection;
	public $streamWrapper;
	public $Disconnected = false;

	public $lastSuccessfulPacket;
	public $PacketQueue = [];
	public $PacketQueueCount = 0;

	public $username;
	public $PlayerEntity;
	public $knownEntities = [];

	public $loadedChunks = [];
	public $chunkRadius = 5;
	public $Inventory;

	public $pktCount = 0;

	public function __construct($connection, $server) {
		$this->uuid = uniqid("client");
		$this->connection = $connection;
		$this->streamWrapper = new StreamWrapper($connection);
		$this->Server = $server;
		$this->World = $server->World;
		$this->Inventory = new InventoryWindow($server->CraftingRepository);
		$this->setItem(0x01, 0x40, 0x00, 3, 0);
		$this->setupPacketListener();
		$this->pktCount = 0;
	}

	public function setupPacketListener() {
		$this->connection->on('data', function ($data) {
			$this->pktCount++;
			$this->streamWrapper->data($data);

			while ($this->pktCount > 0) {
				$this->Server->handlePacket($this);
				$this->pktCount--;
			}
		});
	}

	public function updateChunks() {
		for ($i = 0; $i < 2; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$Coordinates2D = new Coordinates2D($i, $j);

				$chunk = $this->World->generateChunk($Coordinates2D);
				$preamble = new ChunkPreamblePacket($Coordinates2D->x, $Coordinates2D->z);
				$data = $this->createChunkPacket($chunk);
				$this->enqueuePacket($preamble);
				$this->enqueuePacket($data);
			}
		}
	}

	public function createChunkPacket($chunk) {
		$x = $chunk->x;
		$z = $chunk->z;

		$blockdata = $chunk->deserialize();
		$compress = gzcompress($blockdata);

		return new ChunkDataPacket(
			$x,
			0,
			$z,
			$chunk::Width,
			$chunk::Height,
			$chunk::Depth,
			$compress);
	}

	public function enqueuePacket($packet) {
		$this->Server->writePacket($packet, $this);
	}

	public function loadChunk($Coordinates2D) {
		$chunk = $this->World->generateChunk($Coordinates2D);
		$this->enqueuePacket(new ChunkPreamblePacket($chunk->x, $chunk->z));
		$this->enqueuePacket($this->createChunkPacket($chunk));

		$serialized = $chunk->x . ":" . $chunk->z;

		$this->loadedChunks[$serialized] = true;
	}

	public function unloadChunk($Coordinates2D) {
		$this->enqueuePacket(new ChunkPreamablePacket($Coordinates2D->x, $Coordiantes2D->z, false));
		$serialized = $chunk->x . ":" . $chunk->z;
		unset($this->loadedChunks[$serialized]);
		$this->loadedChunks = array_values($array);
	}

	public function disconnect() {
		$this->streamWrapper->close();
		$this->loadedChunks = [];
		$this->connection->handleClose();
	}

	public function disconnectWithReason($reason) {
		$this->loadedChunks = [];
		$this->enqueuePacket(new DisconnectPacket($reason));
		$this->connection->handleClose();
	}

	public function sendMessage($message="") {
		$this->enqueuePacket(new ChatMessagePacket(
			$message
		));
	}

	public function setItem($id=0x00, $amount=0x40, $metadata=0x00, $window=3, $slot=0) {
		$this->Inventory->WindowAreas[$window]->Items[$slot] = new ItemStack($id, $amount, $metadata);
	}
}
