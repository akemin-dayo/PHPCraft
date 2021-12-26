<?php

namespace PHPCraft\Core\Networking\Handlers;

class DataHandler {

	public static function HandleKeepAlive($Packet, $Client, $Server) {
		// When we receive a 0x00 keep-alive packet, reset ticksSinceLastKeepAlive to 0 for the client that sent it

		// It seems that the wiki.vg specification is somewhat incorrect and that actual b1.7.3 clients don't actually send keep-alive packets (0x00) at all.
		// … At least, I wasn't able to find any when I used Wireshark to dump the packet traffic from one.

		// As a result, this functionality is useful only for DirtMultiVersion and any other unofficial client that was written using the wiki.vg spec as a reference.
		// HandleGrounded() in PlayerHandler provides this functionality for b1.7.3 clients.

		$Server->Logger->logDebug("Received a keep-alive packet from " . $Client->username . "'s client!");
		$Client->ticksSinceLastKeepAlive = 0;
	}

	public static function HandleDisconnect($Packet, $Client, $Server) {
		// When we receive a 0xFF disconnect packet from a client, log the reason sent by the client and then call handleDisconnect()
		$Server->Logger->logInfo($Client->username . " is disconnecting from " . $Server->serverName . "…" .  ((mb_strlen($Packet->reason) > 0) ? " (" . $Packet->reason . ")" : ""));
		$Server->handleDisconnect($Client);
	}
}
