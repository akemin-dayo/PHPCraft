<?php

namespace HHVMCraft\Core\Networking;

require "vendor/autoload.php";
require "HHVMCraft.Core/Networking/PacketReader.php";
require "HHVMCraft.Core/Networking/PacketHandler.php";
require "HHVMCraft.Core/Networking/Client.php";

use HHVMCraft\Core\Networking\Client;
use HHVMCraft\Core\Networking\PacketReader;
use HHVMCraft\Core\Networking\PacketHandler;
use HHVMCraft\Core\Networking\Handlers;

use Evenement\EventEmitter;
use React\Socket\Server;

class MultiplayerServer extends EventEmitter {
	public $address;
	public $Clients = [];
	public $PacketHandler;

	public $loop;
	public $socket;
	public $PacketReader;

	public function __construct($address) {
		$this->address = $address;
		$this->loop = \React\EventLoop\Factory::create();
		$this->socket = new Server($this->loop);

		$this->PacketReader = new PacketReader();
		$this->PacketReader->registerPackets();

		$this->PacketHandler = new PacketHandler($this);
	}

	public function acceptClient($connection) {
		array_push($this->Clients, new Client($connection, $this));
	}

	public function start($port) {
		$this->socket->on('connection', function($connection) {
			echo " >> New Connection \n";
			
			$this->acceptClient($connection);	
		});
		
		$this->socket->listen($port);
		$this->loop->run();
		
		echo " >> Listening on address: " + $this->address + ":" + $port + "\n";
	}

	public function handlePacket($client) {
		$packet = $this->PacketReader->readPacket($client);
		if ($packet) {
			$this->PacketHandler->handlePacket($packet, $client, $this);
		} else {
			echo " >> Bad Packet \n";	
		}
	}

}
