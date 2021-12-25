<?php

namespace PHPCraft\Core\Networking\Handlers;

class DataHandler {

	public static function HandleKeepAlive($Packet, $Client, $Server) {
		// When we receive a 0x00 keep-alive packet, reset ticksSinceLastKeepAlive to 0 for the client that sent it

		// TODO (Karen): Perform a packet dump to further investigate communications between a b1.7.3 client and PHPCraft.
		// For some reason, b1.7.3 seems to… not send keep-alive packets to PHPCraft at all…!? DirtMultiVersion does, though.
		// Pain.
		$Server->Logger->logDebug("Received a keep-alive packet from " . $Client->username . "'s client!");
		$Client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandleDisconnect($Packet, $Client, $Server) {
		// When we receive a 0xFF disconnect packet from a client, log the reason sent by the client and then call handleDisconnect()
		$Server->Logger->logInfo($Client->username . " is disconnecting from " . $Server->serverName . "…" .  ((mb_strlen($Packet->reason) > 0) ? " (" . $Packet->reason . ")" : ""));
		$Server->handleDisconnect($Client);
	}
}
