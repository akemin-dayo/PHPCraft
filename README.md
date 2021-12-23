# PHPCraft
###### An open-source Minecraft server implementation, written in PHP.

## What is PHPCraft?

PHPCraft is an open-source Minecraft server implementation, written in PHP.

That being said, PHPCraft is not intended to ever become a fully-functional, production-ready Minecraft server. Rather, it is just a for-fun project that I became interested in one day.

PHPCraft is a fork of [Andrew Vy](https://github.com/andrewvy)'s [HHVMCraft](https://github.com/andrewvy/HHVMCraft) project.

**※ Note:** While HHVMCraft _did_ (as the name would suggest) support HHVM, PHPCraft does not.

---

## Which Minecraft versions are supported?

PHPCraft currently targets and supports Minecraft Beta b1.7.3 ([Beta Protocol 14](https://wiki.vg/Protocol_version_numbers#Beta)), but you can connect to it with modern Minecraft versions (like 1.17.1!) using [DirtMultiVersion](https://github.com/DirtPowered/DirtMultiversion).

(I _would_ like to target/support a more modern Minecraft version at some point, but that would pretty much mean rewriting just about _all_ of the code from scratch and would be a _lot_ of work for what is again, just a project I'm doing for fun because I found it interesting.)

**※ Note:** Bedrock Edition clients (using [Geyser](https://github.com/GeyserMC/Geyser)) do not work correctly yet with PHPCraft for some reason (all blocks are invisible), despite the fact that Geyser _does_ work just fine with DirtMultiVersion when connected to other servers.

---

## How do I run PHPCraft?

### Prerequisites
* PHP 8.x (recommended) or 7.x
	* `brew install php` on macOS (requires [Homebrew](https://brew.sh/) to be installed)
	* `sudo apt install php` on Debian-based Linux distributions
	* [PHP for Windows](https://windows.php.net/download)
		* The "Non Thread Safe" version is sufficient for our purposes, as PHPCraft only uses PHP on the CLI, and not as part of a web server.
* Composer
	* `brew install composer` on macOS (requires [Homebrew](https://brew.sh/) to be installed)
	* Follow [these instructions](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) on Linux
	* Use [the Composer installer](https://getcomposer.org/Composer-Setup.exe) on Windows

### Running PHPCraft
```shell
git clone https://github.com/akemin-dayo/PHPCraft.git
cd PHPCraft
composer install
php server.php
```

You can configure both the server port (`25565` by default) and the IP address to bind to (`0.0.0.0` by default) in `server.php`!

---

## Useful developer resources
* [Protocol specification for Minecraft Beta b1.7.3 (Beta Protocol 14) on wiki.vg (oldid 510)](https://wiki.vg/index.php?title=Protocol&oldid=510)
	* This is the last page revision pertaining to Beta Protocol 14, made on 2011/08/14.

---

## License

Licensed under the [MIT License](https://opensource.org/licenses/MIT).
