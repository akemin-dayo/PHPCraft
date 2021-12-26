<?php

namespace PHPCraft\Core\Networking\Handlers;

use PHPCraft\API\Coordinates3D;
use PHPCraft\Core\Entities\PlayerEntity;
use PHPCraft\Core\Networking\Packets\SetPlayerPositionPacket;
use PHPCraft\Core\Networking\Packets\RespawnPacket;
use PHPCraft\Core\Networking\Packets\BlockChangePacket;
use PHPCraft\Core\Networking\Packets\UpdateHealthPacket;

// This import is required since it contains the BIG_ENDIAN constant, which is used in some debugging output code below
use PHPCraft\Core\Networking\StreamWrapper;

class PlayerHandler {

	public static function HandleGrounded($Packet, $Client, $Server) {
		// When we receive a 0x0A PlayerGroundedPacket, reset ticksSinceLastKeepAlive to 0 for the client that sent it

		// It seems that the wiki.vg specification is somewhat incorrect and that actual b1.7.3 clients don't actually send keep-alive packets (0x00) at all.
		// … At least, I wasn't able to find any when I used Wireshark to dump the packet traffic from one.

		// As a result, this functionality is handled here specifically for b1.7.3 clients.

		// For what it's worth, PHPCraft also handles 0x00 keep-alive packets, but in DataHandler HandleKeepAlive().
		// DirtMultiVersion and any other unofficial clients written using the wiki.vg spec as a reference /do/ send the 0x00 keep-alive packets, so…
		// $Server->Logger->logDebug("Received a PlayerGroundedPacket from " . $Client->username . "'s client!");
		$Client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandlePosition($packet, $client, $server) {
		// TODO (vy): Actually do server-side checking for position
		$client->PlayerEntity->Position->x = $packet->x;
		$client->PlayerEntity->Position->y = $packet->y;
		$client->PlayerEntity->Position->z = $packet->z;
		// If we receive a PlayerPositionPacket, the client is quite obviously still alive.
		// Because of COURSE b1.7.3 clients stop sending PlayerGroundedPackets when they're jumping. Pain.
		$client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandleLook($Packet, $Client, $Server) {
		$Client->PlayerEntity->Position->pitch = $Packet->pitch;
		$Client->PlayerEntity->Position->yaw = $Packet->yaw;
		// Same thing as above but for PlayerLookPacket.
		$Client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandlePositionAndLook($Packet, $Client, $Server) {
		$Client->PlayerEntity->Position->x = $Packet->x;
		$Client->PlayerEntity->Position->y = $Packet->y;
		$Client->PlayerEntity->Position->z = $Packet->z;
		$Client->PlayerEntity->Position->pitch = $Packet->pitch;
		$Client->PlayerEntity->Position->yaw = $Packet->yaw;
		// It's PlayerPositionAndLookPacket this time. You get the point.
		$Client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandleRespawn($Packet, $Client, $Server) {
		$spawnpoint = $Client->World->ChunkProvider->spawnpoint;

		$Client->enqueuePacket(new SetPlayerPositionPacket(
			$spawnpoint->x,
			$spawnpoint->y,
			$spawnpoint->y + PlayerEntity::Height,
			$spawnpoint->z,
			0,
			0,
			true)
		);

		$Client->enqueuePacket(new RespawnPacket());
		$Client->enqueuePacket(new UpdateHealthPacket(20));
	}

	public static function HandleBlockPlacement($Packet, $Client, $Server) {
		// DIRECTION
		//   0   1   2   3   4   5
		//  -Y	+Y	-Z	+Z	-X	+X

		$direction = $Packet->direction;
		$x = $Packet->x;
		$y = $Packet->y;
		$z = $Packet->z;

		switch ($direction) {
			case 0:
				$y--;
				break;
			case 1:
				$y++;
				break;
			case 2:
				$z--;
				break;
			case 3:
				$z++;
				break;
			case 4:
				$x--;
				break;
			case 5:
				$x++;
				break;
			default:
				return 0;
		}

		$targetBlockCoordinates = new Coordinates3D($x, $y, $z);

		/*
			Simply using sprintf('0x%02X', $Packet->blockid) will not work correctly here.

			This is because all integers in PHP are actually 64-bit (or 32-bit on 32-bit hosts).
			Basically, a concept of a short (16-bit / 2-byte integer) simply does not exist.

			As a result of this, when $Packet->blockid is -1…
			It'll get converted to 0xFFFFFFFFFFFFFFFF, which is the 64-bit two's complement for -1.

			With the below implementation however, we can successfully get a 16-bit two's complement for -1 (0xFFFF).
				1. Pack $Packet->blockid into a binary blob of type `short` using pack()
				2. If running on a little-endian system (you probably are), reverse the string using strrev()
					※ This requires the BIG_ENDIAN const from PHPCraft\Core\Networking\StreamWrapper
				3. Convert the binary blob into a hex string using bin2hex()
				4. Convert the hex string into a decimal string using hexdec()
				5. Feed the result into sprintf('0x%02X') which gives us nice, clean output.
		*/
		$blockid_shortBinBlob = pack("s", $Packet->blockid);
		$Server->Logger->logDebug($Client->username . " placed or used block/item ID " . $Packet->blockid . " (" . sprintf('0x%02X', hexdec(bin2hex((BIG_ENDIAN) ? $blockid_shortBinBlob : strrev($blockid_shortBinBlob)))) . ") at " . $targetBlockCoordinates->toString());

		// This check must be performed against the decimal representation of -1 instead of the 16-bit two's complement representation of 0xFFFF.
		// The reason as to why is… described above in that massive comment block. ;P
		if ($Packet->blockid == -1) {
			$Server->Logger->logWarning("Interacting with blocks/items hasn't been implemented yet!");
			return $Client->sendMessage("Interacting with blocks/items hasn't been implemented yet!");
		}

		if (!$Server->EntityManager->checkForBlockingEntities($targetBlockCoordinates)) {
			$Server->World->setBlockID($targetBlockCoordinates, $Packet->blockid);
			$broadcastPacket = new BlockChangePacket($x, $y, $z, $Packet->blockid, 0x00);
			$Server->broadcastPacket($broadcastPacket);
		}
	}

	public static function HandleDigging($Packet, $Client, $Server) {
		$status = $Packet->status;
		$x = $Packet->x;
		$y = $Packet->y;
		$z = $Packet->z;

		$coords = new Coordinates3D($x, $y, $z);

		$face = $Packet->face;

		switch ($status) {
			case 0:
				return 0;
				break;
			case 2:
				$slot_index = $Client->Inventory->findEmptySpace();

				// Translate block coordinates to chunk coordinates
				// Fetch the chunk that contains that block coords
				// Get the block id from the chunk
				// Find empty space where we can increment or add w/ the block id
				// Update the player inventory with the block id

				if ($slot_index > -1) {
				}

				$Server->World->setBlockID($coords, 0x00);
				$broadcastPacket = new BlockChangePacket($x, $y, $z, 0x00, 0x00);
				$Server->broadcastPacket($broadcastPacket);

				$Server->Logger->logDebug($Client->username . " broke a block at " . $coords->toString());
				break;
			case 4:
				return 0;
				break;
			default:
				return 0;
		}
	}
}
