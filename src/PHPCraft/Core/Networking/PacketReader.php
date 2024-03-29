<?php
/**
 * PacketReader does not actually read the packet, the actual packet reading happens in the Client's StreamWrapper.
 * It also registers all known packets so a packet can be recognized and decoded, and wraps the packet enqueuing for clients.
 */
namespace PHPCraft\Core\Networking;

use PHPCraft\Core\Networking\Packets;

class PacketReader {
	public $protocol_version;
	public $ServerboundPackets = [];
	public $ClientboundPackets = [];

	public function __construct($protocol_version = 14) {
		$this->protocol_version = $protocol_version;
		$this->ServerboundPackets = new \SplFixedArray(256);
		$this->ClientboundPackets = new \SplFixedArray(256);
	}

	public function registerPackets() {
		// Register new packet type. type: packet, serverbound: bool, clientbound: bool.
		$this->registerPacketType('Packets\KeepAlivePacket');
		$this->registerPacketType('Packets\LoginRequestPacket', true, false);
		$this->registerPacketType('Packets\LoginResponsePacket', false, true);
		$this->registerPacketType('Packets\HandshakePacket', true, false);
		$this->registerPacketType('Packets\HandshakeResponsePacket', false, true);
		$this->registerPacketType('Packets\ChatMessagePacket');
		$this->registerPacketType('Packets\TimeUpdatePacket', false, true);
		$this->registerPacketType('Packets\EntityEquipmentPacket', false, true);
		$this->registerPacketType('Packets\SpawnPositionPacket', false, true);
		$this->registerPacketType('Packets\UseEntityPacket', true, false);
		$this->registerPacketType('Packets\UpdateHealthPacket', false, true);
		$this->registerPacketType('Packets\RespawnPacket');
		$this->registerPacketType('Packets\PlayerGroundedPacket', true, false);
		$this->registerPacketType('Packets\PlayerPositionPacket', true, false);
		$this->registerPacketType('Packets\PlayerLookPacket', true, false);
		$this->registerPacketType('Packets\PlayerPositionAndLookPacket', true, false);
		$this->registerPacketType('Packets\SetPlayerPositionPacket', false, true);
		$this->registerPacketType('Packets\HoldingChangePacket', true, false);
		$this->registerPacketType('Packets\EntityActionPacket', true, false);
		$this->registerPacketType('Packets\PlayerDiggingPacket', true, false);
		$this->registerPacketType('Packets\PlayerBlockPlacementPacket', true, false);
		$this->registerPacketType('Packets\UseBedPacket', false, true);
		$this->registerPacketType('Packets\AnimationPacket');
		$this->registerPacketType('Packets\SpawnPlayerPacket', false, true);
		$this->registerPacketType('Packets\PickupSpawnPacket', false, true);
		$this->registerPacketType('Packets\CollectItemPacket', false, true);
		// $this->registerPacketType(Packets\SpawnGenericEntityPacket, false, true);
		// $this->registerPacketType(Packets\SpawnMobPacket, false, true);
		// $this->registerPacketType(Packets\SpawnPaintingPacket, false, true);

		$this->registerPacketType('Packets\EntityVelocityPacket', false, true);
		// $this->registerPacketType(Packets\DestroyEntityPacket, false, true);
		// $this->registerPacketType(Packets\UselessEntityPacket, false, true);
		$this->registerPacketType('Packets\EntityRelativeMovePacket', false, true);
		// $this->registerPacketType(Packets\EntityLookPacket, false, true);
		// $this->registerPacketType(Packets\EntityLookAndRelativeMovePacket, false, true);
		$this->registerPacketType('Packets\EntityTeleportPacket', false, true);

		$this->registerPacketType('Packets\EntityStatusPacket', false, true);
		$this->registerPacketType('Packets\AttachEntityPacket', false, true);
		$this->registerPacketType('Packets\EntityMetadataPacket', false, true);

		$this->registerPacketType('Packets\ChunkPreamblePacket', false, true);
		$this->registerPacketType('Packets\ChunkDataPacket', false, true);
		// $this->registerPacketType(Packets\BulkBlockChangePacket, false, true);
		$this->registerPacketType('Packets\BlockChangePacket', false, true);
		// $this->registerPacketType(Packets\BlockActionPacket, false, true);

		// $this->registerPacketType(Packets\ExplosionPacket, false, true);
		$this->registerPacketType('Packets\SoundEffectPacket', false, true);

		// $this->registerPacketType(Packets\EnvironmentStatePacket, false, true);
		// $this->registerPacketType(Packets\LightningPacket, false, true);

		$this->registerPacketType('Packets\OpenWindowPacket', false, true);
		$this->registerPacketType('Packets\CloseWindowPacket');
		// $this->registerPacketType(Packets\ClickWindowPacket, true, false);
		$this->registerPacketType('Packets\SetSlotPacket', false, true);
		$this->registerPacketType('Packets\WindowItemsPacket', false, true);
		$this->registerPacketType('Packets\UpdateProgressBarPacket', false, true);
		// $this->registerPacketType(Packets\TransactionStatusPacket);

		$this->registerPacketType('Packets\UpdateSignPacket');
		// $this->registerPacketType(Packets\MapDataPacket, false, true);

		// $this->registerPacketType(Packets\UpdateStatisticPacket, false, true);

		$this->registerPacketType('Packets\DisconnectPacket');

	}

	public function registerPacketType($type, $serverbound = true, $clientbound = true) {
		if ($serverbound) {
			$this->ServerboundPackets[constant('PHPCraft\Core\Networking\\' . $type . '::id')] = $type;
		}
		if ($clientbound) {
			$this->ClientboundPackets[constant('PHPCraft\Core\Networking\\' . $type . '::id')] = $type;
		}
	}

	public function readPacket($client, $serverbound = true) {
		$id = $client->streamWrapper->readInt8();
		$type = null;

		if ($serverbound && isset($this->ServerboundPackets[$id])) {
			$type = $this->ServerboundPackets[$id];
		} else if (isset($this->ClientboundPackets[$id])) {
			$type = $this->ClientboundPackets[$id];
		} else if ($id == -1) {
			$type = $this->ServerboundPackets[0xFF];
		}

		if (is_null($type)) {
			$client->Server->Logger->logError("Unrecognised packet ID: " . $id . " (" . sprintf('0x%02X', $id) . ")");
			return;
		}

		$construct = "PHPCraft\\Core\\Networking\\" . $type;

		$packet = new $construct();
		$packet->readPacket($client->streamWrapper);

		return $packet;
	}

	public function writePacket($packet, $client) {
		/*
			TODO (Karen): Figure out how to implement failed packet retry functionality in a sane way.

			The commented-out failed packet retry functionality below has some major issues with it:
			・There is no form of throttling whatsoever for the retried packets, so it will actually retry sending packets as fast as it possibly can, severely impacting server performance.
				・This is most easily noticeable when using the /stime command. Notice how much slower the time moves!
			・If a client loses connection and times out, any packets that failed to send to the timed-out client will be stuck retrying infinitely EVEN AFTER the client is disconnected by the server!
				・This effectively prevents the Client object for disconnected clients from being destroyed.

			That being said, removing the retry functionality entirely isn't really a good solution either, because that would cause clients that momentarily lose connection to miss world/block updates (which is bad for obvious reasons).
				・At least, I think it would. I haven't actually tested to see if this is the case yet, but it makes sense in theory.

			tl;dr: pain pain pain pain pain pain pain pain
		*/
		/*
		if (!($packet->writePacket($client->streamWrapper))) {
			$client->enqueuePacket($packet);
		}
		*/

		$packet->writePacket($client->streamWrapper);
	}
}
