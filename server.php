<?php
date_default_timezone_set('UTC');

require "vendor/autoload.php";

use PHPCraft\Core\Networking\MultiplayerServer;
error_reporting(E_ALL);

$addr = "127.0.0.1";

$server = new MultiplayerServer($addr);
$server->start(25565);
