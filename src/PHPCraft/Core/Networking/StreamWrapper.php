<?php
/**
* The actual dirty bit manipulation.
* StreamWrapper provides a nice wrapper to read and write packets to/from the stream.
*/

namespace PHPCraft\Core\Networking;

use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Helpers\Logger;

// https://stackoverflow.com/questions/16039751/php-pack-format-for-signed-32-int-big-endian
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

class StreamWrapper {
	public $Server;
	public $stream;
	public $streamBuffer;

	public function __construct($stream, $server) {
		$this->stream = $stream;
		$this->streamBuffer = [];
		$this->Server = $server;
	}

	public function data($data) {
		if ($this->Server->packetDumpingEnabled) {
			echo(Logger::COLOUR_CYANBOLD . "[READ PACKET FROM CLIENT - FILL BUFFER] " . Logger::COLOUR_RESET);
			Hex::dump($data);
		}

		$arr = array_reverse(str_split(bin2hex($data), 2));
		$this->streamBuffer = array_merge($this->streamBuffer, $arr);
	}

	public function read($len) {
		$s = "";
		for ($i = 0; $i < $len; $i++) {
			$s = $s . hex2bin(array_pop($this->streamBuffer));
		}

		if ($this->Server->packetDumpingEnabled) {
			echo(Logger::COLOUR_YELLOWBOLD . "[READ PACKET FROM CLIENT - READ FROM BUFFER] " . Logger::COLOUR_RESET);
			Hex::dump($s);
		}
		return $s;
	}

	public function writePacket($data) {
		if ($this->Server->packetDumpingEnabled) {
			echo(Logger::COLOUR_GREENBOLD . "[WRITE PACKET TO CLIENT] " . Logger::COLOUR_RESET);
			Hex::dump($data);
		}

		$res = $this->stream->write($data);
		if ($res != false) {
			return true;
		} else {
			return false;
		}
	}

	public function close() {
		$this->streamBuffer = [];
	}

	/* Type Mappings begin here */
	// PHP pack() format reference: https://www.php.net/manual/en/function.pack.php

	/*
		byte (signed 8-bit integer)
		PHP pack() format: signed char
	*/
	public function readInt8() {
		return unpack("c", $this->read(1))[1];
	}
	public function writeInt8($data) {
		return pack("c", $data);
	}
	// Convenience aliases
	public function readByte() {
		return $this->readInt8();
	}
	public function writeByte($data) {
		return $this->writeInt8($data);
	}
	/* ******************************** */

	/*
		bool
		PHP pack() format: signed char
	*/
	public function readBool() {
		return (bool)$this->readInt8();
	}
	public function writeBool($data) {
		return $this->writeInt8(((bool)$data) ? 0x01 : 0x00);
	}
	/* ******************************** */

	/*
		short (signed 16-bit integer)
		PHP pack() format: unsigned short (always 16 bit, big endian byte order)

		TODO (Karen) / WARNING: The pack() format being used here seems incorrect! (sign)
	*/
	public function readInt16() {
		return unpack("n", $this->read(2))[1];
	}
	public function writeInt16($data) {
		return pack("n", $data);
	}
	// Convenience aliases
	public function readShort() {
		return $this->readInt16();
	}
	public function writeShort($data) {
		return $this->writeInt16($data);
	}
	/* ******************************** */

	/*
		int (signed 32-bit integer)
		PHP pack() format for READ: unsigned long (always 32 bit, big endian byte order)
		PHP pack() format for WRITE: signed long (always 32 bit, machine byte order)

		TODO (Karen) / WARNING: The READ pack() format being used here seems incorrect! (sign)
	*/
	public function readInt() {
		return unpack("N", $this->read(4))[1];
	}
	public function writeInt($data) {
		if (BIG_ENDIAN) {
			return pack('l', $data);
		}
		return strrev(pack("l", $data));
	}
	/* ******************************** */

	/*
		long (signed 64-bit integer)
		PHP pack() format: signed long long (always 64 bit, machine byte order)

		TODO (Karen) / WARNING: These functions do not seem to handle endianness correctly!
	*/
	public function readLong() {
		return unpack("q", $this->read(8))[1];
	}
	public function writeLong($data) {
		return pack("q", $data);
	}
	/* ******************************** */

	/*
		float (32-bit float, single-precision)
		PHP pack() format: float (machine dependent size and representation)

		※ NOTE: PHP 7.0.15+ and 7.1.1+ (both 2017/01/19) added the "G" format, which is "float (machine dependent size, big endian byte order)"
			Consider switching to that instead.
	*/
	public function readFloat() {
		return unpack("f", strrev($this->read(4)))[1];
	}
	public function writeFloat($data) {
		if (BIG_ENDIAN) {
			return pack("f", $data);
		}
		return strrev(pack("f", $data));
	}
	/* ******************************** */

	/*
		double (64-bit float, double-precision)
		PHP pack() format: double (machine dependent size and representation)

		※ NOTE: PHP 7.0.15+ and 7.1.1+ (both 2017/01/19) added the "E" format, which is "double (machine dependent size, big endian byte order)"
			Consider switching to that instead.
	*/
	public function readDouble() {
		return unpack("d", strrev($this->read(8)))[1];
	}
	public function writeDouble($data) {
		if (BIG_ENDIAN) {
			return pack("d", $data);
		}
		return strrev(pack("d", $data));
	}
	/* ******************************** */

	/*
		string8 (mUTF-8, with a prefixed short containing the string length)

		Reference: https://wiki.vg/index.php?title=Protocol&oldid=510#Data_Types
		See also: https://en.wikipedia.org/wiki/UTF-8#Modified_UTF-8
	*/
	public function readString8() {
		$expectedStringLength = $this->readShort();
		$constructedString = "";

		for ($i = 0; $i < $expectedStringLength; $i++) {
			$constructedString = $constructedString . chr($this->readShort());
		}

		// TODO (Karen): Properly convert from mUTF-8 to UTF-8 here.

		if (strlen($constructedString) > 0) {
			return $constructedString;
		} else {
			// TODO (Karen): Add proper error handling for when the string somehow ends up empty.
		}
	}
	public function writeString8WithoutStringLengthShort($str) {
		// TODO (Karen): Actually implement UTF-8 to mUTF-8 string conversion here.
		return $str;
	}
	/* ******************************** */

	/*
		string16 (UCS-2 big endian, with a prefixed short containing the string length)

		Reference: https://wiki.vg/index.php?title=Protocol&oldid=510#Data_Types
	*/
	public function readString16() {
		$expectedStringLength = $this->readShort();
		$constructedString = "";

		for ($i = 0; $i < $expectedStringLength; $i++) {
			$constructedString = $constructedString . chr($this->readShort());
		}

		if (strlen($constructedString) > 0) {
			return $constructedString;
		} else {
			// TODO (Karen): Add proper error handling for when the string somehow ends up empty.
		}
	}
	public function writeString16WithoutStringLengthShort($str) {
		$str = iconv("UTF-8", "UCS-2BE", $str);
		return $str;
	}
	/* ******************************** */
}
