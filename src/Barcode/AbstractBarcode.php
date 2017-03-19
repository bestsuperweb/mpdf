<?php

namespace Mpdf\Barcode;

abstract class AbstractBarcode
{

	/**
	 * @var mixed[]
	 */
	protected $data;

	/**
	 * @return mixed[]
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getKey($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
	}

	/**
	 * @return string
	 */
	public function getChecksum()
	{
		return $this->getKey('checkdigit');
	}

	/**
	 * Convert binary barcode sequence to barcode array
	 *
	 * @param string $seq
	 * @param mixed[] $barcodeData
	 *
	 * @return mixed[]
	 */
	protected function binseqToArray($seq, array $barcodeData)
	{
		$len = strlen($seq);
		$w = 0;
		$k = 0;
		for ($i = 0; $i < $len; ++$i) {
			$w += 1;
			if (($i == ($len - 1)) or (($i < ($len - 1)) and ($seq[$i] != $seq[($i + 1)]))) {
				if ($seq[$i] == '1') {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$barcodeData['bcode'][$k] = ['t' => $t, 'w' => $w, 'h' => 1, 'p' => 0];
				$barcodeData['maxw'] += $w;
				++$k;
				$w = 0;
			}
		}
		return $barcodeData;
	}

	/**
	 * Convert large integer number to hexadecimal representation.
	 * (requires PHP bcmath extension)
	 *
	 * @param int $number
	 * @return string
	 */
	public function decToHex($number)
	{
		if (!function_exists('bcmod')) {
			throw new \Mpdf\MpdfException('Barcode library requires bcmath extension to be loaded.');
		}

		$i = 0;
		$hex = [];
		if ($number == 0) {
			return '00';
		}
		while ($number > 0) {
			if ($number == 0) {
				array_push($hex, '0');
			} else {
				array_push($hex, strtoupper(dechex(bcmod($number, '16'))));
				$number = bcdiv($number, '16', 0);
			}
		}
		$hex = array_reverse($hex);
		return implode($hex);
	}

	/**
	 * Convert large hexadecimal number to decimal representation (string).
	 * (requires PHP bcmath extension)
	 *
	 * @param string $hex
	 * @return int
	 */
	protected function hexToDec($hex)
	{
		if (!function_exists('bcadd')) {
			throw new \Mpdf\MpdfException('Barcode library requires bcmath extension to be loaded.');
		}

		$dec = 0;
		$bitval = 1;
		$len = strlen($hex);
		for ($pos = ($len - 1); $pos >= 0; --$pos) {
			$dec = bcadd($dec, bcmul(hexdec($hex[$pos]), $bitval));
			$bitval = bcmul($bitval, 16);
		}
		return $dec;
	}

}
