<?php

declare(strict_types=1);

namespace Stu\Lib;

use DOMDocument;
use DOMXPath;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\ElementNode;
use JBBCode\Parser;
use JBBCode\TextNode;
use JBBCode\validators\CssColorValidator;
use JBBCode\visitors\HTMLSafeVisitor;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Colony\ColonyMessageBbCodeParser;
use Stu\StuTestCase;

final class StuBbCodeColorDefinitionTest extends StuTestCase
{
    #[DataProvider('attackInputs')]
    public function testUntrustedOptionsCannotCreateBrowserAttributes(string $consumer, string $input): void
    {
        $parser = $this->createParser($consumer);
        $parser->parse($input);
        if ($consumer === 'message') {
            $parser->accept(new HTMLSafeVisitor());
        }
        $html = $parser->getAsHTML();
        $document = new DOMDocument();
        @$document->loadHTML('<div>' . $html . '</div>');
        $xpath = new DOMXPath($document);

        self::assertSame(0, $xpath->query('//*[@onmouseover or @onclick or @onload or @onerror]')->length);
        self::assertSame(0, $xpath->query('//span')->length);
        self::assertStringNotContainsString('&amp;quot;', $html);
    }

    public static function attackInputs(): iterable
    {
        foreach (['plain', 'message', 'profile', 'colony'] as $consumer) {
            foreach (['color', 'COLOR', 'CoLoR'] as $tag) {
                yield $consumer . '-' . $tag . '-quote' => [$consumer, '[' . $tag . '=red" onmouseover="alert(1)]Text[/color]'];
                yield $consumer . '-' . $tag . '-tab' => [$consumer, '[' . $tag . "=red\"\tonmouseover=\"alert(1)]Text[/color]"];
            }
        }
    }

    #[DataProvider('validInputs')]
    public function testValidColorsAndNestedFormattingRemainAvailable(string $consumer, string $tag, string $color): void
    {
        $parser = $this->createParser($consumer);
        $parser->parse('[' . $tag . '=' . $color . '][b]Text[/b][i]kursiv[/i][u]Linie[/u][/color]');
        if ($consumer === 'message') {
            $parser->accept(new HTMLSafeVisitor());
        }

        self::assertSame(
            '<span style="color: ' . $color . '"><strong>Text</strong><em>kursiv</em><u>Linie</u></span>',
            $parser->getAsHTML()
        );
    }

    public static function validInputs(): iterable
    {
        foreach (['plain', 'message', 'profile', 'colony'] as $consumer) {
            foreach (['color', 'COLOR', 'CoLoR'] as $tag) {
                foreach (['red', '#12AbEF', 'rgb(1,2,3)', 'rgba(1,2,3,0.5)'] as $color) {
                    yield [$consumer, $tag, $color];
                }
            }
        }
    }

    public function testUnknownAndDuplicateOptionKeysAreRejectedAndEscaped(): void
    {
        $definition = new CodeDefinitionBuilder('color', '<span style="color: {option}">{param}</span>')
            ->setUseOption(true)
            ->setOptionValidator(new CssColorValidator())
            ->build();
        foreach ([[], ['onclick' => 'red'], ['color' => 'red', 'COLOR' => 'blue'], ['COLOR' => '<img src=x onerror=alert(1)>']] as $attributes) {
            $element = new ElementNode();
            $element->setCodeDefinition($definition);
            $element->setAttribute($attributes);
            $element->addChild(new TextNode('<script>alert(1)</script>'));

            self::assertFalse($definition->hasValidInputs($element));
            self::assertStringNotContainsString('<', $element->getAsHTML());
        }
    }

    public function testExistingEscapingIsNotAppliedTwiceToInvalidOptionsOrText(): void
    {
        $parser = $this->createParser('plain');
        $parser->parse('[COLOR=red" onclick="alert(1)]A & B < C[/color]')->accept(new HTMLSafeVisitor());
        $html = $parser->getAsHTML();

        self::assertStringContainsString('A &amp; B &lt; C', $html);
        self::assertStringNotContainsString('&amp;amp;', $html);
        self::assertStringNotContainsString('&amp;lt;', $html);
        self::assertStringNotContainsString('&amp;quot;', $html);
    }

    private function createParser(string $consumer): Parser
    {
        if ($consumer === 'colony') {
            return new ColonyMessageBbCodeParser();
        }
        $parser = new Parser();
        $parser->addCodeDefinitionSet($consumer === 'profile' ? new StuBbCodeWithImageDefinitionSet() : new StuBbCodeDefinitionSet());

        return $parser;
    }
}
