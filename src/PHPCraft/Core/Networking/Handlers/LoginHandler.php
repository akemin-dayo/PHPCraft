<?php

namespace PHPCraft\Core\Networking\Handlers;

use PHPCraft\Core\Entities\PlayerEntity;
use PHPCraft\Core\Networking\Packets\ChatMessagePacket;
use PHPCraft\Core\Networking\Packets\HandshakeResponsePacket;
use PHPCraft\Core\Networking\Packets\LoginResponsePacket;
use PHPCraft\Core\Networking\Packets\SetPlayerPositionPacket;
use PHPCraft\Core\Networking\Packets\SpawnPositionPacket;
use PHPCraft\Core\Networking\Packets\TimeUpdatePacket;
use PHPCraft\Core\Networking\Packets\WindowItemsPacket;
use PHPCraft\Core\Networking\Packets\UpdateHealthPacket;

class LoginHandler {

	public static function HandleHandshake($packet, $client, $server) {
		$client->username = $packet->username;

		// Sends the string "-" to indicate that no account authentication should take place.
		$client->enqueuePacket(new HandshakeResponsePacket("-"));
	}

	public static function HandleLoginRequest($packet, $client, $server) {
		// Make sure the client has the right protocol version before allowing them to connect.
		if ($packet->protocolVersion == 14) {

			// Respond with details about the world.
			$client->enqueuePacket(new LoginResponsePacket(0, 0, 0));

			// Add player entity to entitymanager, subscribe client to entities.
			$client->PlayerEntity = $server->EntityManager->addPlayerEntity($client);

			// Handle client inventory.. (WindowItemPacket)
			$client->enqueuePacket(new WindowItemsPacket(0, $client->Inventory->getSlots()));

			// Set the player entity position to the world's spawnpoint
			$client->PlayerEntity->Position = $client->World->ChunkProvider->spawnpoint;

			// Send packaet that sets the player's spawnpoint to that location.
			$client->enqueuePacket(new SpawnPositionPacket(
				$client->PlayerEntity->Position->x,
				$client->PlayerEntity->Position->y,
				$client->PlayerEntity->Position->z)
			);

			// send packet that actually sets the player's current position to that position.
			$client->enqueuePacket(new SetPlayerPositionPacket(
				$client->PlayerEntity->Position->x + 5,
				$client->PlayerEntity->Position->y + PlayerEntity::Height - 5,
				$client->PlayerEntity->Position->y,
				$client->PlayerEntity->Position->z+15,
				0,
				0,
				0)
			);

			// Send the world time to the client.
			$client->enqueuePacket(new TimeUpdatePacket(
				$server->World->getTime())
			);

			// Send a full health packet to the client.
			$client->enqueuePacket(new UpdateHealthPacket(20));

			// Begin sending chunk data.
			$client->updateChunks();

			$server->Logger->throwLog("Added new client!");
			$server->sendMessage($client->username . " has joined the server!");

		} else {
			// The client's version is not the same as this server implementation.
			// So, we should disconnect that client with a 'Wrong Version' message.
			$server->Logger->throwWarning("Wrong client version!");
			$server->handleDisconnect($client, true, "Wrong client version!");
		}
	}
}
