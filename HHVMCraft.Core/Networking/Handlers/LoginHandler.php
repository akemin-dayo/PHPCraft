<?php

namespace HHVMCraft\Core\Networking\Handlers;

require "HHVMCraft.Core/Networking/Packets/HandshakeResponsePacket.php";
require "HHVMCraft.Core/Networking/Packets/LoginResponsePacket.php";
require "HHVMCraft.Core/Helpers/HexDump.php";

use HHVMCraft\Core\Helpers\Hex;
use HHVMCraft\Core\Networking\Packets;

class LoginHandler {

	public static function HandleHandshakePacket($packet, $client, $server) {
		$client->username = $packet->username;

		$client->enqueuePacket(new Packets\HandshakeResponsePacket("-"));
	}

	public static function HandleLoginRequestPacket($packet, $client, $server) {
		if ($packet->protocolVersion == 14) {

			// Respond with details about the world.
			$client->enqueuePacket(new Packets\LoginResponsePacket(0, 0, 0));	
	
			// Handle client inventory.. (WindowItemPacket)
				
			// Creating player entity..
			$client->createPlayerEntity();


			// Handle client entity spawnpoint.. (SpawnPositionPacket)
			// Handle player position (SetPlayerPositionPacket)
			// Handle client time (TimeUpdatePacket)

			// Add player entity to entitymanager, subscribe client to entities.
		} else {
			throw new \Exception("Wrong version!");
		}
	}
}
