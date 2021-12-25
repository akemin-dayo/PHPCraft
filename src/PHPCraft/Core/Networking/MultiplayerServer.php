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
use PHPCraft\Core\Networking\Packets\TimeUpdatePacket;
use PHPCraft\Core\World\World;
use React\Socket\Server;

class MultiplayerServer extends EventEmitter {
	public $address;
	public $serverName;
	public $shouldPreventClientTimeDrift;
	public $packetDumpingEnabled;

	public $Clients = [];

	public $PacketHandler;
	public $PacketReader;
	public $EntityManager;
	public $World;

	public $loop;
	public $socket;

	public $tickRate = 1 / 20; // 20 ticks per second (TPS)

	public function __construct($address, $serverName, $shouldPreventClientTimeDrift, $packetDumpingEnabled) {
		$this->address = $address;
		$this->serverName = $serverName;
		$this->shouldPreventClientTimeDrift = $shouldPreventClientTimeDrift;
		$this->packetDumpingEnabled = $packetDumpingEnabled;

		$this->loop = \React\EventLoop\Loop::get();

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
		$this->socket = new Server($this->address . ":" . $port, $this->loop);

		$this->socket->on('connection', function ($connection) {
			$this->Logger->logInfo("A new client is connecting!");
			$this->acceptClient($connection);
		});

		$this->loop->addPeriodicTimer($this->tickRate, function () {
			$this->EntityManager->update();
			$this->World->updateTime();

			/*
				b1.7.3 clients will crash with an NPE if a TimeUpdatePacket is received before the client joins the world.
				This means that if a TimeUpdatePacket is broadcasted on every tick, b1.7.3 will be completely unable to join the server.

				Broadcasting a TimeUpdatePacket once every second significantly decreases the chances of a b1.7.3 client crashing.
				That should probably…? be good enough to prevent client-side time drift (or at least prevent it from getting too bad).

				PHPCraft doesn't have any fancy features like TPS adjustment that would make this an issue (yet), anyway.
				(Some servers let you set the TPS higher than 20, so time goes by faster.)

				That being said… b1.7.3 clients can still crash if they just so happen to join while the packet is being broadcasted.
				As a result, I'm disabling the TimeUpdatePacket broadcast by default for now until I can figure out how to properly fix that.

				Having the broadcast be disabled /does/ cause clients to drift away from server time though, as expected. Pain.

				(Basically need to come up with some mechanism to broadcast TimeUpdatePackets only to clients that have fully joined the world…)
			*/

			// $this->broadcastPacket(new TimeUpdatePacket($this->World->getTime()));
		});

		$this->loop->addPeriodicTimer(1, function () {
			$this->emitKeepAlive();
			// Broadcast a TimeUpdatePacket every second to correct any client-side time drift.
			// Disabled by default, see above for more information as to why.
			if ($this->shouldPreventClientTimeDrift) {
				$this->broadcastPacket(new TimeUpdatePacket($this->World->getTime()));
			}
		});

		$this->Logger->logInfo($this->serverName . " is listening on address: " . $this->address . ":" . $port);
		if ($this->packetDumpingEnabled) {
			$this->Logger->logWarning("Packet logging is enabled! This is useful only for developer debugging, and generates a lot of log output.");
		}
		$this->loop->run();
	}

	public function acceptClient($connection) {
		$client = new Client($connection, $this);
		$this->Clients[$client->uuid] = $client;
	}

	public function handlePacket($client) {
		$self = $this;
		$this->loop->futureTick(function() use ($self, $client) {
			$packet = $self->PacketReader->readPacket($client);

			if ($packet) {
				$self->loop->futureTick(function() use ($self, $packet, $client) {
					$self->PacketHandler->handlePacket($packet, $client, $self);
				});
			}
		});
	}

	public function writePacket($packet, $client) {
		$self = $this;

		$this->loop->futureTick(function() use ($self, $packet, $client) {
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

		$this->sendMessage($Client->username . " has disconnected from " . $this->serverName);
	}

	public function emitKeepAlive() {
		foreach ($this->Clients as $Client) {
			$Client->enqueuePacket(new KeepAlivePacket());
		}
	}

	public function sendMessage($message="") {
		$this->Logger->logInfo($message);

		foreach ($this->Clients as $Client) {
			$Client->enqueuePacket(new ChatMessagePacket(
				$message
			));
		}
	}
}
