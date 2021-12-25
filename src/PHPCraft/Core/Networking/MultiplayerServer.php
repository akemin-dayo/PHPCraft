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
use PHPCraft\Core\Networking\Packets\TimeUpdatePacket;
use PHPCraft\Core\World\World;
use React\Socket\Server;

class MultiplayerServer extends EventEmitter {
	public $address;
	public $serverName;
	public $packetDumpingEnabled;

	public $Clients = [];

	public $PacketHandler;
	public $PacketReader;
	public $EntityManager;
	public $World;

	public $loop;
	public $socket;

	public $tickRate = 1 / 20; // 20 ticks per second (TPS)

	public function __construct($address, $serverName, $packetDumpingEnabled) {
		$this->address = $address;
		$this->serverName = $serverName;
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
			$this->Logger->logInfo("Disconnecting " . $Client->username . " from " . $this->serverName . "…" . ((mb_strlen($reason) > 0) ? " (" . $reason . ")" : ""));
			$Client->disconnectWithReason($reason);
		} else {
			// We already handle the logging for this in DataHandler (upon 0xFF disconnect packet)
			$Client->disconnect();
		}

		$Client->connection->handleClose();
		$Client->connection->close();

		/* Cleaning up after client disconnection */
		// Cancel all async timer loops
		$this->loop->cancelTimer($Client->sendClientBoundKeepAliveTimer);
		$this->loop->cancelTimer($Client->isClientStillAliveTimer);
		if (!is_null($Client->sendTimeUpdatePacketToPreventTimeDriftTimer)) {
			// sendTimeUpdatePacketToPreventTimeDriftTimer requires an additional is_null check.
			// This is because it is only initialised after the client sends a LoginRequestPacket (0x01) in response to a HandshakeResponsePacket (0x02).
			$this->loop->cancelTimer($Client->sendTimeUpdatePacketToPreventTimeDriftTimer);
		} else {
			$this->Logger->logDebug($Client->username . "'s client never sent a LoginRequestPacket (0x01) in response to our HandshakeResponsePacket (0x02), so sendTimeUpdatePacketToPreventTimeDriftTimer was never initialised (and therefore does not need to be cancelled and destroyed).");
		}

		// We must unset() all the timers before attempting to unset() the Client object.
		// Otherwise, the Client object will not be destroyed.
		unset($Client->sendClientBoundKeepAliveTimer);
		unset($Client->isClientStillAliveTimer);
		unset($Client->sendTimeUpdatePacketToPreventTimeDriftTimer);

		// Finally destroy the Client object.
		unset($this->Clients[$Client->uuid]);
		/* ************************************** */

		$this->sendMessage($Client->username . " has disconnected from " . $this->serverName);
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
