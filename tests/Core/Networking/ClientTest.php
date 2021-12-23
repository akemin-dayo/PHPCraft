<?php

use Evenement\EventEmitter;
use PHPCraft\Core\Networking\MultiplayerServer;
use PHPCraft\Core\Networking\Client;
use PHPUnit\Framework\TestCase;

/*

class FakeConnection extends EventEmitter {
}

class FakeServer {
	public $CraftingRepository;

	public function handlePacket($client) {
		return true;
	}
}

class ClientTest extends TestCase {
	public function testClientCreation() {
		$server = new MultiplayerServer(25565);
		$connection = new FakeConnection();
		$client = new Client($connection, $server);
	}
}

*/
