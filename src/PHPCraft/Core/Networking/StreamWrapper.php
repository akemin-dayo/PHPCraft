<?php
/**
* The actual dirty bit manipulation.
* StreamWrapper provides a nice wrapper to read and write packets to/from the stream.
*/

namespace PHPCraft\Core\Networking;

use PHPCraft\Core\Helpers\Hex;
use PHPCraft\Core\Helpers\Logger;

// https://www.php.net/manual/en/function.pack.php
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

	public function readInt8() {
		return unpack("c", $this->read(1))[1];
	}

	public function writeInt8($data) {
		return pack("c", $data);
	}

	public function readBool() {
		return (bool) $this->readInt8();
	}

	public function writeBool($data) {
		if ($data == true) {
			$this->writeInt8(0x01);
		} else {
			$this->writeInt8(0x00);
		}
	}

	public function readInt16() {
		return unpack("n", $this->read(2))[1];
	}

	public function writeInt16($data) {
		return pack("n*", $data);
	}

	public function readInt() {
		return unpack("N", $this->read(4))[1];
	}

	public function writeInt($data) {
		if (BIG_ENDIAN) {
			return pack('l', $data);
		}

		return strrev(pack("l*", $data));
	}

	public function readLong() {
		return unpack("q", $this->read(8))[1];
	}

	public function writeLong($data) {
		return pack("q*", $data);
	}

	public function readString16() {
		$l = $this->readInt16();
		$str = "";

		for ($i = 0; $i < $l; $i++) {
			$str = $str . chr($this->readInt16());
		}

		if (strlen($str) > 0) {
			return $str;
		} else {
			// No string found?
		}
	}

	public function writeString16($str) {
		$str = iconv("UTF-8", "UTF-16BE", $str);

		return $str;
	}

	public function readDouble() {
		return unpack("d", strrev($this->read(8)))[1];
	}

	public function writeDouble($data) {
		if (BIG_ENDIAN) {
			return pack("d*", $data);
		}

		return strrev(pack("d", $data));
	}
}
