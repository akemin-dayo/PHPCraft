# PHPCraft
###### An open-source Minecraft: Java Edition server implementation, written in PHP.

## What is PHPCraft?

PHPCraft is an open-source Minecraft: Java Edition server implementation, written in PHP.

That being said, PHPCraft is not intended to ever become a fully-functional, production-ready Minecraft server. It is simply a project that I work on for fun in my free time, because the idea of writing a Minecraft server implementation in _PHP_ is just _really_ amusing to me.

PHPCraft is a fork of [Andrew Vy](https://github.com/andrewvy)'s [HHVMCraft](https://github.com/andrewvy/HHVMCraft) project.

**※ Note:** While HHVMCraft _did_ (as the name would suggest) support HHVM, PHPCraft does not.

---

## Which Minecraft versions are supported?

PHPCraft currently targets and supports Minecraft Beta b1.7.3 ([Beta/pre-Netty Protocol 14](https://wiki.vg/Protocol_version_numbers#Beta)), but you can connect to it with modern Minecraft versions using [ViaProxy](https://github.com/RaphiMC/ViaProxy) (recommended) or [DirtMultiVersion](https://github.com/DirtPowered/DirtMultiversion) — [see below](#screenshots) for screenshots!

I _would_ have targeted and supported a modern Minecraft version if I were writing PHPCraft from scratch today, but since this is based off of an existing project that already had a large amount of work done for b1.7.3 already… supporting a modern version would have pretty much required a full rewrite and would be a _lot_ of work for what is again, just a project I'm doing for fun in my free time.

(Maybe if I ever somehow just have _too_ much free time some day, perhaps… ;P)

**※ Regarding Bedrock Edition clients connecting using [Geyser](https://github.com/GeyserMC/Geyser):** Geyser may not work correctly with PHPCraft (all the blocks become invisible for some unknown reason). I'm not sure if this is still an issue with recent versions of Geyser, though.

---

## Screenshots

![A screenshot of Minecraft 1.17.1 connected to a PHPCraft server, showing a small house that was built from wood planks, a small pond, a tree, and various rose and dandelion flowers scattered about.](screenshots/Minecraft%201.17.1%20-%2001%20Day.png)
<p align="center">A modern Minecraft 1.17.1 client connected to a PHPCraft server. A Minecraft Beta b1.7.3 client was also connected to the PHPCraft server at the same time, viewing the same world.<br><br><em>(There are no doors or beds because those blocks do not work correctly yet in PHPCraft.)</em></p>

![A screenshot of Minecraft 1.17.1 connected to a PHPCraft server, showing the interior of a small house that was built from wood planks. It is later in the day, and the sunlight is filtering through the glass roof. There are two furnaces, some bookshelves, a crafting table, a music player, a chair (actually an oak stair block), and a single stone slab intended to represent a desk.](screenshots/Minecraft%201.17.1%20-%2006%20Morning%20(Interior).png)
<p align="center">The same Minecraft 1.17.1 client connected to the same PHPCraft server, but at a later time of day.</p>

More screenshots can be found in the [screenshots folder](screenshots/) that include more times of day, as well as screenshots of the Minecraft Beta b1.7.3 client that was also connected to the same PHPCraft server at the time.

---

## How do I run PHPCraft?

### Prerequisites
* PHP 8.x (recommended, actively used in development) or PHP 7.4 (older versions also work, but are not recommended — [see below](#if-youre-using-php-7) for more information)
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
php start.php # ./start.php also works
```

You can configure various options in `start.php`, including (but not limited to) both the server port (`25565` by default) and the IP address to bind to (`0.0.0.0` by default)!

### If you're using PHP 7…
Consider using PHP 8 instead. The only reason why PHPCraft supports PHP 7 because it just so happens to work. I actively develop and test against PHP 8.

That being said, if you _really must_ use PHP 7 for some reason…

* **PHP 7.4:** Run `composer update -W`.
* **PHP 7.3 (end-of-life):** Run `composer update -W`.
* **PHP 7.2 (end-of-life):** Run `composer require --no-update --dev phpunit/phpunit ^8` and then `composer update -W`.
* **PHP 7.1 and 7.0 (end-of-life):** Run `composer require --no-update monolog/monolog ^1`, `composer require --no-update --dev phpunit/phpunit ^6`, and then finally `composer update -W`.

**※ IMPORTANT:** Please be aware that PHP 7 is [no longer actively supported](https://www.php.net/supported-versions.php). PHP 7.4 is only receiving critical security updates (no bug fixes), and PHP 7.3〜7.0 have completely reached end-of-life and are **no longer receiving any security updates whatsoever.**

---

## Useful developer resources
* [Protocol specification for Minecraft Beta b1.7.3 (Beta/pre-Netty Protocol 14) on wiki.vg (oldid 510)](https://wiki.vg/index.php?title=Protocol&oldid=510)
	* This is the last page revision pertaining to Beta/pre-Netty Protocol 14, made on 2011/08/14.

---

## License

Licensed under the [MIT License](https://opensource.org/licenses/MIT).
