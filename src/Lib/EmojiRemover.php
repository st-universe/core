<?php

namespace Stu\Lib;


class EmojiRemover
{
	public static function clearEmojis(string $text): string
	{
		$text = iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
		$text = preg_replace('/\s+/', ' ', $text);

		return iconv('ISO-8859-15', 'UTF-8', $text);
	}
}
