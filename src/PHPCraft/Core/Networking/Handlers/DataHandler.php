<?php

namespace PHPCraft\Core\Networking\Handlers;

class DataHandler {

	public static function HandleKeepAlive() {
		// Do nothing for now
	}

	public static function HandleDisconnect($Packet, $Client, $Server) {
		// If called, this means we've read a serverbound packet that a client has disconnected.

		$Server->Logger->logInfo("Client has disconnected with reason: " . $Packet->reason);

		$Server->handleDisconnect($Client);
	}
}
