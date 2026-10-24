<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\TextNode;
use JBBCode\visitors\HTMLSafeVisitor;

final class StuBbCodeHtmlSafeVisitor extends HTMLSafeVisitor
{
    #[\Override]
    public function visitTextNode(TextNode $textNode): void
    {
        $parts = preg_split('/<br[ \t\r\n\f]*\/?>/i', $textNode->getValue()) ?: [$textNode->getValue()];
        $textNode->setValue(implode('<br />', array_map(
            static fn (string $part): string => htmlspecialchars(
                html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ),
            $parts
        )));
    }
}
