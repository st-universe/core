<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Dom\HTMLDocument;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class KnEditorEscapingTest extends StuTestCase
{
    public function testStoredTitleAndBodyRemainLiteralEditorValues(): void
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../src'), ['autoescape' => 'html']);
        foreach ([['Ein normaler Titel', '[b]Ein erlaubter Beitrag[/b]'],
            ['Titel" autofocus onfocus="window.test=1', '</textarea><img src=x onerror="window.test=1">[b]Beitrag[/b]']] as [$title, $text]) {
            $html = $twig->render('html/communication/editKn.twig', [
                'javascriptPath' => '/static', 'SESSIONSTRING' => 'test',
                'POST' => ['getId' => 1, 'getTitle' => $title, 'getText' => $text, 'getPlotId' => null],
                'CHARACTER_IDS_STRING' => ''
            ]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            self::assertSame($title, $document->querySelector('input[name="title"]')->getAttribute('value'));
            self::assertSame($text, $document->querySelector('textarea')->textContent);
            self::assertCount(1, $document->querySelectorAll('textarea'));
            self::assertCount(0, $document->querySelectorAll('img, [onfocus], [onerror], [autofocus]'));
        }
    }
}
