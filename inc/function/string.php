<?php

setlocale(LC_ALL, 'fr_FR.UTF8');

function url_transform($str, $delimiter = '-')
{
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return trim($clean, '-');
}

function truncate($str, $length)
{
	$length = abs((int)$length);
	if(strlen($str) > $length) {
		$str = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $str);
	}
	return $str;
}

function str_ends_with($str, $strEnd)
{
	if (empty($str)) {
		return false;
	}
	return substr_compare($str, $strEnd, -strlen($strEnd), strlen($strEnd)) === 0;
}