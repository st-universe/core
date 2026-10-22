<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\DocumentElement;
use JBBCode\ElementNode;
use JBBCode\NodeVisitor;
use JBBCode\TextNode;

final class StuBbCodeImageVisitor implements NodeVisitor
{
    #[\Override]
    public function visitDocumentElement(DocumentElement $documentElement): void
    {
        $this->visitElementNode($documentElement);
    }

    #[\Override]
    public function visitTextNode(TextNode $textNode): void {}

    #[\Override]
    public function visitElementNode(ElementNode $elementNode): void
    {
        if ($elementNode->getTagName() !== 'img') {
            foreach ($elementNode->getChildren() as $child) {
                $child->accept($this);
            }
            return;
        }

        $url = '';
        foreach ($elementNode->getChildren() as $child) {
            $url .= $child->getAsBBCode();
        }

        if (!new StuBbCodeImageValidator()->validate($url)) {
            $html = htmlspecialchars($elementNode->getAsBBCode(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else {
            $html = sprintf(
                '<img src="%s" style="max-height: 100%%;max-width:100%%;" />',
                htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }

        foreach ($elementNode->getChildren() as $child) {
            $elementNode->removeChild($child);
        }
        $elementNode->addChild(new TextNode($html));
    }
}
