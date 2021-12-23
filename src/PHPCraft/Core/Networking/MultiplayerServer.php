<?php
/**
* MultiplayerServer is the central networking and game loop controller.
* It handles:
* - The game loop and entity loop.
* - The connection/disconnection of clients.
* - Packets to be read.
*/
namespace PHPCraft\Core\Networking;

use Evenement\EventEmitter;

use PHPCraft\API\BlockRepository;
use PHPCraft\API\CraftingRepository;
use PHPCraft\Core\Entities\EntityManager;
use PHPCraft\Core\Helpers\Logger;
use PHPCraft\Core\Networking\Handlers;
use PHPCraft\Core\Networking\PackerReader;
use PHPCraft\Core\Networking\Packets\ChatMessagePacket;
use PHPCraft\Core\Networking\Packets\KeepAlivePacket;
use PHPCraft\Core\World\World;
use React\Socket\Server;

class MultiplayerServer extends EventEmitter {
	public $address;
	public $Clients = [];

	public $PacketHandler;
	public $PacketReader;
	public $EntityManager;
	public $World;

	public $loop;
	public $socket;

	public $tickRate = 1 / 20; // 20 ticks per second (TPS)

	public function __construct($address) {
		$this->address = $address;
		$this->loop = \React\EventLoop\Factory::create();
		$this->socket = new Server($this->loop);

		$this->PacketReader = new PacketReader();
		$this->PacketReader->registerPackets();

		$this->BlockRepository = new BlockRepository();
		$this->CraftingRepository = new CraftingRepository();

		$this->PacketHandler = new PacketHandler($this);
		$this->World = new World("Flatland", $this->BlockRepository);

		$this->EntityManager = new EntityManager($this, $this->World);

		$this->Logger = new Logger();
	}

	public function start($port) {
		$this->socket->on('connection', function ($connection) {
			$this->Logger->throwLog("New Connection");
			$this->acceptClient($connection);
		});

		$this->socket->listen($port, $this->address);

		$this->loop->addPeriodicTimer($this->tickRate, function () {
			$this->EntityManager->update();
		});

		$this->loop->addPeriodicTimer(1, function () {
			$this->emitKeepAlive();
			$this->World->updateTime();
		});

		$this->Logger->throwLog("Listening on address: " . $this->address . ":" . $port);
		$this->loop->run();
	}

	public function acceptClient($connection) {
		$client = new Client($connection, $this);
		$this->Clients[$client->uuid] = $client;
	}

	public function handlePacket($client) {
		$self = $this;
		$this->loop->nextTick(function() use ($self, $client) {
			$packet = $self->PacketReader->readPacket($client);

			if ($packet) {
				$self->loop->nextTick(function() use ($self, $packet, $client) {
					$self->PacketHandler->handlePacket($packet, $client, $self);
				});
			}
		});
	}

	public function writePacket($packet, $client) {
		$self = $this;

		$this->loop->nextTick(function() use ($self, $packet, $client) {
			$self->PacketReader->writePacket($packet, $client);
		});
	}

	public function broadcastPacket($packet) {
		foreach ($this->Clients as $Client) {
			$Client->enqueuePacket($packet);
		}
	}

	public function handleDisconnect($Client, $ServerOriginated = false, $reason="") {
		if ($ServerOriginated) {
			$Client->disconnectWithReason($reason);
		} else {
			$Client->disconnect();
		}

		$Client->connection->handleClose();
		$Client->connection->close();

		unset($this->Clients[$Client->uuid]);

		$this->sendMessage($Client->username." has disconnected from the server.");
	}

	public function emitKeepAlive() {
		foreach ($this->Clients as $Client) {
			$Client->enqueuePacket(new KeepAlivePacket());
		}
	}

	public function sendMessage($message="") {
		$this->Logger->throwLog($message);

		foreach ($this->Clients as $Client) {
			$Client->enqueuePacket(new ChatMessagePacket(
				$message
			));
		}
	}
}
