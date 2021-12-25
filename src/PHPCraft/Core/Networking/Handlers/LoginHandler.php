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

		// Send a connection hash of "-" to indicate that no account authentication should take place.
		$client->enqueuePacket(new HandshakeResponsePacket("-"));
	}

	public static function HandleLoginRequest($packet, $client, $server) {
		// Make sure that the client actually has the right (pre-Netty) protocol version before allowing them to connect.
		if ($packet->protocolVersion == 14) {

			// Respond with details about the world.
			$client->enqueuePacket(new LoginResponsePacket(0, 0, 0));

			// Add PlayerEntity to EntityManager and subscribe the client to entities.
			$client->PlayerEntity = $server->EntityManager->addPlayerEntity($client);

			// Handle client inventory (WindowItemPacket).
			$client->enqueuePacket(new WindowItemsPacket(0, $client->Inventory->getSlots()));

			// Set the player entity position to the world's spawnpoint.
			$client->PlayerEntity->Position = $client->World->ChunkProvider->spawnpoint;

			// Send a packet that sets the player's spawnpoint to the world's spawnpoint.
			$client->enqueuePacket(new SpawnPositionPacket(
				$client->PlayerEntity->Position->x,
				$client->PlayerEntity->Position->y,
				$client->PlayerEntity->Position->z)
			);

			// Send a packet that actually sets the player's current position to that position.
			$client->enqueuePacket(new SetPlayerPositionPacket(
				$client->PlayerEntity->Position->x + 5,
				$client->PlayerEntity->Position->y + PlayerEntity::Height - 5,
				$client->PlayerEntity->Position->y,
				$client->PlayerEntity->Position->z+15,
				0,
				0,
				0)
			);

			// Initialise and start sendTimeUpdatePacketToPreventTimeDriftTimer
			$client->sendTimeUpdatePacketToPreventTimeDriftTimer = $server->loop->addPeriodicTimer($server->tickRate, function () use ($client, $server) {
				// Sends a new TimeUpdatePacket containing the current world time on every tick to the client.
				// This prevents the client time from drifting out of sync with the server (as well as other clients).
				// (Minecraft clients will increment the world time on the client-side, even if no TimeUpdatePackets are actually sent.)
				$client->enqueuePacket(new TimeUpdatePacket($server->World->getTime()));
			});

			// Send a full health packet to the client.
			$client->enqueuePacket(new UpdateHealthPacket(20));

			// Begin sending chunk data.
			$client->updateChunks();

			$server->Logger->logInfo("Added a new client!");
			$server->sendMessage($client->username . " has joined " . $server->serverName . "!");
			$server->sendMessage("Welcome to " . $server->serverName . ", " . $client->username . "!");
		} else {
			// If the client version is incorrect, disconnect said client with a message indicating what version they should use instead.
			// TODO (Karen): Fix all the client-bound disconnect messages, since they actually don't work right now.
			$server->Logger->logError("Wrong client version! A client attempted to connect using Beta/pre-Netty protocol version " . $packet->protocolVersion . "!");
			$server->handleDisconnect($client, true, "Wrong client version (" . $packet->protocolVersion . ")! This server supports Minecraft Beta b1.7.3 (Beta Protocol 14).");
		}
	}
}
