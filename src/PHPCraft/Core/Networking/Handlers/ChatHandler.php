<?php

namespace PHPCraft\Core\Networking\Handlers;

use PHPCraft\API\Coordinates3D;
use PHPCraft\Core\Networking\Packets\WindowItemsPacket;
use PHPCraft\Core\Networking\Packets\UpdateHealthPacket;
use PHPCraft\Core\Networking\Packets\BlockChangePacket;
use PHPCraft\Core\Networking\Packets\TimeUpdatePacket;

class ChatHandler {
	public static function HandleChatMessage($Packet, $Client, $Server) {
		if ($Packet->message[0] == "/") {
			self::handleCommand($Packet->message, $Client, $Server);
		} else {
			$message = "<" . $Client->username . "> " . $Packet->message;
			$Server->sendMessage($message);
		}
	}

	public static function handleCommand($message, $Client, $Server) {
		$args = explode(" ", $message);
		$args_count = count($args);

		switch ($args[0]) {
			case "/help":
			case "/?":
				$Client->sendMessage("--- PHPCraft Help ---");
				$Client->sendMessage("/buffer: Shows the current size of the buffer for this client.");
				$Client->sendMessage("/echo <text>: Prints whatever you type after it.");
				$Client->sendMessage("/getpos: Shows information about your current position.");
				$Client->sendMessage("/give <block/item ID> [quantity]: Gives you blocks or items.");
				$Client->sendMessage("/heart: <3");
				$Client->sendMessage("/help or /?: Shows a list of commands.");
				$Client->sendMessage("/kill or /suicide: Kills you.");
				$Client->sendMessage("/ping: Pong!");
				$Client->sendMessage("/rename <name>: Changes your name.");
				$Client->sendMessage("/sethealth <0-20>: Sets your health value.");
				$Client->sendMessage("/time [preset or numerical]: Shows or sets the world time.");
				$Client->sendMessage("/stime [preset or numerical]: The same as /time, but smooth.");
				$Client->sendMessage("/version: Shows information about the PHPCraft server.");
				break;
			case "/buffer":
				$Client->sendMessage("Buffer size for this client: " . count($Client->streamWrapper->streamBuffer) . " bytes");
				break;
			case "/ping":
				$Client->sendMessage("Pong!");
				break;
			case "/kill":
			case "/suicide":
				$Server->sendMessage($Client->username . " tripped over their own foot and died");
				$Client->enqueuePacket(new UpdateHealthPacket(0));
				break;
			case "/sethealth":
				if (!is_numeric($args[1])) {
					$Client->sendMessage("A health value can only include numbers!");
					$Client->sendMessage("Usage: /sethealth <0-20>");
					break;
				}

				$targetHealth = (int)$args[1];
				if ($targetHealth == 0) {
					$Server->sendMessage($Client->username . " flopped over and died from some unknown malady");
				} else if ($targetHealth > 0 && $targetHealth <= 20) {
					$Client->sendMessage("Successfully set your health to " . $targetHealth . "!");
				} else {
					$Client->sendMessage("A health value must be between 0 to 20 (inclusive)!");
					$Client->sendMessage("Usage: /sethealth <0-20>");
					break;
				}
				$Client->enqueuePacket(new UpdateHealthPacket($targetHealth));
				break;
			case "/getpos":
				$playerCoordinates = $Client->PlayerEntity->Position->toString();
				$playerYaw = $Client->PlayerEntity->Position->yaw;
				$playerPitch = $Client->PlayerEntity->Position->pitch;

				$Client->sendMessage("Position: " . $playerCoordinates);
				$Client->sendMessage("Yaw (Rotation, left-right): " . $playerYaw);
				$Client->sendMessage("Pitch (Head angle, up-down): " . $playerPitch);
				break;
			case "/give":
				if ($args_count == 1 || !is_numeric($args[1])) {
					$Client->sendMessage("A numerical block/item ID is required!");
					$Client->sendMessage("Usage: /give <block/item ID> [quantity]");
					$Client->sendMessage("Valid block IDs are 1-96, and valid item IDs are 256-359.");
					break;
				}

				if ($args_count == 3 && !is_numeric($args[2])) {
					$Client->sendMessage("Item quantity can only include numbers!");
					$Client->sendMessage("Usage: /give <block/item ID> [quantity]");
					$Client->sendMessage("Valid block IDs are 1-96, and valid item IDs are 256-359.");
					break;
				} else if ($args_count == 3) {
					$item_count = (int)$args[2];
				} else {
					$item_count = 64;
				}

				// Officially speaking, Minecraft should only support stacking items up to 64 in one stack.
				// That being said, it does seem like clients /can/ handle up to a maximum of 127 items in one stack for some reason.
				if ($item_count > 127) {
					$item_count = 127;
				}

				$blockOrItemID = (int)$args[1];

				// Valid block IDs in b1.7.3: 1-96
				// Valid item IDs in b1.7.3: 256-359
				if (($blockOrItemID > 0 && $blockOrItemID < 97) || ($blockOrItemID > 255 && $blockOrItemID < 360)) {
					$Client->setItem($blockOrItemID, $item_count);
					$Client->enqueuePacket(new WindowItemsPacket(0, $Client->Inventory->getSlots()));
					$Client->sendMessage("Gave " . $item_count . " of block/item ID " . $blockOrItemID . " to " . $Client->username);
				} else {
					$Client->sendMessage("The specified block/item ID (" . $blockOrItemID . ") is not valid.");
					$Client->sendMessage("Valid block IDs are 1-96, and valid item IDs are 256-359.");
				}
				break;
			case "/heart":
				$Client->sendMessage("<3");
				break;
			case "/rename":
				if ($args_count < 2) {
					$Client->sendMessage("You must specify a name!");
					$Client->sendMessage("Usage: /rename <name>");
					break;
				}

				$desiredName = $args[1];

				if (strlen($desiredName) > 16) {
					$Client->sendMessage("Your name is too long! Names must be 16 characters or less.");
					break;
				}

				$Server->sendMessage($Client->username . " has changed their name to " . $desiredName);

				$Client->username = $desiredName;
				$Client->PlayerEntity->username = $desiredName;
				break;
			case "/version":
			case "/ver":
			case "/about":
			case "/icanhasphpcraft":
				// TODO (Karen): Make this actually show the Git version info.
				$Client->sendMessage("This server is running PHPCraft (MC: b1.7.3 / Beta Protocol 14)");
				break;
			case "/time":
			case "/stime":
			case "/smoothtime":
			case "/times":
			case "/timesmooth":
				if ($args_count == 1) {
					$Client->sendMessage("The current world time is " . $Server->World->getTime() . " ticks.");
					break;
				}

				$desiredTime = $args[1];

				// Convert preset times to tick values
				if (!is_numeric($desiredTime)) {
					switch($desiredTime) {
						case "day":
							$desiredTime = 0;
							break;
						case "morning":
							$desiredTime = 1000;
							break;
						case "default": // This is the default time used in PHPCraft (as defined in World.php)
							$desiredTime = 4020;
							break;
						case "noon":
							$desiredTime = 6000;
							break;
						case "sunset":
							$desiredTime = 12000;
							break;
						case "night":
							$desiredTime = 14000;
							break;
						default:
							$Client->sendMessage($desiredTime . " is not a valid time preset!");
							$Client->sendMessage("Presets: day (0), morning (1000), default (4020), noon (6000), sunset (12000), night (14000)");
							return;
					}
				}

				$terminologyToUseForCompletionMessage = "set";
				if ($args[0] == "/time") {
					$Server->World->setTime($desiredTime);
					$Server->broadcastPacket(new TimeUpdatePacket($Server->World->getTime()));
				} else {
					/*
						/stime (and its aliases) works by incrementing the world time at 20000 uPS until the desired time is reached.
						This is achieved without changing the server tickrate, which remains at 20 TPS at all times.

						The result is a nice and smooth day transition effect, somewhat akin to "fast-forwarding" the world.
						
						/stime works best on modern Minecraft versions, as b1.7.3 makes the time transition take much longer.
						(It seems like b1.7.3 is unable to keep up with the incoming TimeUpdatePackets…)
					*/
					$terminologyToUseForCompletionMessage = "smoothly set";
					// Begin a fast, 20000 uPS loop to smoothly change the time
					$hasTimeBeenReached = false;
					$smoothTimeLoop = $Server->loop->addPeriodicTimer(1 / 20000, function () use ($Server, $desiredTime, &$hasTimeBeenReached) {
						if (!$hasTimeBeenReached) {
							$Server->World->updateTime();
							$Server->broadcastPacket(new TimeUpdatePacket($Server->World->getTime()));
							if ($Server->World->getTime() == $desiredTime) {
								$hasTimeBeenReached = true;
							}
						}
					});
					// Destroy the loop after 10 seconds, which should be enough time to reach the target time.
					$Server->loop->addTimer(10.0, function () use ($Server, $smoothTimeLoop) {
						$Server->loop->cancelTimer($smoothTimeLoop);
					});
				}

				$Client->sendMessage("The world time was " . $terminologyToUseForCompletionMessage . " to " . $desiredTime . " ticks!");
				break;
			case "/echo":
				$constructedEchoMessage = "";
				if ($args_count > 1) {
					// PHP implicitly makes a copy when assigned like this.
					$inputStringArray = $args;
					array_shift($inputStringArray);
					$constructedEchoMessage = implode(" ", $inputStringArray);
				}
				$Client->sendMessage($constructedEchoMessage);
				break;
			default:
				$Client->sendMessage($args[0] . " is not a valid command!");
		}
	}

	// TODO (Karen): Refactor and move all commands into their own functions (or even classes for more complex ones…?)
}
