#!/usr/bin/env php
<?php
require "vendor/autoload.php";
use PHPCraft\Core\Networking\MultiplayerServer;

error_reporting(E_ALL);
date_default_timezone_set('UTC');

/* PHPCraft Configuration */

// The IP address that PHPCraft will bind to.
// Set this to 127.0.0.1 if you want PHPCraft to only be accessible on the local machine it is running on.
$server_bind_ip = "0.0.0.0";

// The port that PHPCraft will listen on.
// Set this to another port (such as 25564 or 25563) if you want to run PHPCraft behind DirtMultiVersion or another proxy like it.
$server_port = 25565;

// The name of the server, which shows up in places like the join/welcome/quit messages.
$server_name = "PHPCraft";

// If set to true, enables packet dumping. This is useful only for developer debugging, and generates a lot of log output.
$enable_packet_dumping = false;

/* ********************** */

$server = new MultiplayerServer(
	$server_bind_ip,
	$server_name,
	$enable_packet_dumping
);
$server->start($server_port);