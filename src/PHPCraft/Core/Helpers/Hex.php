<?php
/**
 * Hex provides a useful dump functionality to inspect packets.
 */
namespace PHPCraft\Core\Helpers;

class Hex {
	public static function dumpNoEcho($data, $newline = "\n") {
		static $from = '';
		static $to = '';

		static $width = 16; // number of bytes per line

		static $pad = '.'; // padding for non-visible characters

		if ($from === '') {
			for ($i = 0; $i <= 0xFF; $i++) {
				$from .= chr($i);
				$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
			}
		}

		$hex = str_split(bin2hex($data), $width * 2);
		$chars = str_split(strtr($data, $from, $to), $width);

		$offset = 0;
		$finalHexdumpString = "";
		foreach ($hex as $i => $line) {
			$finalHexdumpString = $finalHexdumpString . sprintf('%6X', $offset) . ' : ' . implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
			$offset += $width;
		}
		return $finalHexdumpString;
	}

	public static function dump($data, $newline = "\n") {
		echo(Hex::dumpNoEcho($data, $newline));
	}
}
