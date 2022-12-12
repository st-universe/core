<?php

namespace Stu\Lib;


class CleanTextUtils
{
	public static function clearEmojis(string $text): string
	{
		$text = iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
		$text = preg_replace('/\s+/', ' ', $text);

		return iconv('ISO-8859-15', 'UTF-8', $text);
	}

	public static function clearUnicode(string $text): string
	{
		return preg_replace('/&#?\d+;/', '', $text);
	}

	public static function checkBBCode($str): bool
	{
		$taglist = array("b", "i", "u", "color"); //the bb-tags to search for

		foreach ($taglist as $tag) {
			// How often is the open tag?  
			preg_match_all('/\[[ ]?' . $tag . '[ ]?(=[ ]?[^ ]+?[ ]?)?\]/i', $str, $matches);
			$opentags = count($matches['0']);

			// How often is the close tag?  
			preg_match_all('/\[\/' . $tag . '\]/i', $str, $matches);
			$closetags = count($matches['0']);

			// how many tags have been unclosed? 
			$diff = abs($opentags - $closetags);

			if ($diff > 0) {
				return false;
			}
		}

		return true;
	}
}
