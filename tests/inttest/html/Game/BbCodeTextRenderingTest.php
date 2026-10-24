<?php

declare(strict_types=1);

namespace Stu\Html\Game;

use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\TwigTestCase;
use Twig\Environment;

final class BbCodeTextRenderingTest extends TwigTestCase
{
    #[DataProvider('formattedTexts')]
    public function testTextFormattingRemainsSafe(string $text, string $expected): void
    {
        $twig = $this->getContainer()->get(Environment::class);

        self::assertSame($expected, $twig->createTemplate('{{ text|bbcode }}')->render(['text' => $text]));
    }

    public static function formattedTexts(): iterable
    {
        yield 'named-entities' => ['&alpha; &Omega; &copy;', 'α Ω ©'];
        yield 'numeric-entities' => ['&#945; &#x3B1;', 'α α'];
        yield 'nested-formatting' => ['[color=yellow][b]&alpha;[/b][/color]', '<span style="color: yellow"><strong>α</strong></span>'];
        yield 'quotes-and-ampersands' => ['A &amp; B &quot;C&quot; &apos;D&apos;', 'A &amp; B &quot;C&quot; &#039;D&#039;'];
        yield 'one-decoding-pass' => ['&amp;alpha; &amp;lt;script&amp;gt;', '&amp;alpha; &amp;lt;script&amp;gt;'];
        yield 'encoded-bbcode-stays-text' => ['&#91;b&#93;Text&#91;/b&#93;', '[b]Text[/b]'];
        yield 'encoded-html-stays-text' => ['&lt;img src=x onerror=alert(1)&gt;', '&lt;img src=x onerror=alert(1)&gt;'];
        yield 'numeric-html-stays-text' => ['&#60;svg onload=alert(1)&#62;', '&lt;svg onload=alert(1)&gt;'];
        yield 'literal-breaks' => ['A<br>B<BR/>C<br />D', 'A<br />B<br />C<br />D'];
        yield 'encoded-break-stays-text' => ['A&lt;br&gt;B', 'A&lt;br&gt;B'];
        yield 'break-attributes-stay-text' => ['A<br onclick="alert(1)">B', 'A&lt;br onclick=&quot;alert(1)&quot;&gt;B'];
        yield 'break-slash-attribute-stays-text' => ['A<br/onmouseover=alert(1)>B', 'A&lt;br/onmouseover=alert(1)&gt;B'];
        yield 'encoded-break-angle-stays-text' => ['A&#60;br>B', 'A&lt;br&gt;B'];
        yield 'nested-legacy-break' => ['[color=red]A<br>B[/color]', '<span style="color: red">A<br />B</span>'];
        yield 'encoded-color-option-rejected' => ['[color=red&quot; onclick=&quot;alert(1)]Text[/color]', '[color=red&amp;quot; onclick=&amp;quot;alert(1)]Text[/color]'];
        yield 'invalid-utf8' => ["Text\xFF", 'Text�'];
    }

    public function testNamesKeepTheirEntitiesAndStoredValues(): void
    {
        $twig = $this->getContainer()->get(Environment::class);
        $name = '[b]&alpha;[/b] &Omega; &lt;img src=x onerror=alert(1)&gt;';

        foreach ([new Ship(), new Station(), new Colony()] as $entity) {
            $entity->setName($name);
            $html = $twig->createTemplate('<div id="name">{{ entity.getName|bbcode }}</div>')->render(['entity' => $entity]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $element = $document->getElementById('name');

            self::assertNotNull($element);
            self::assertSame('α Ω <img src=x onerror=alert(1)>', $element->textContent);
            self::assertSame('α', $element->getElementsByTagName('strong')->item(0)?->textContent);
            self::assertSame(0, $element->getElementsByTagName('img')->length);
            self::assertSame($name, $entity->getName());
        }
    }

    public function testPlainNamesRemainSafeInPageTitlesAndAttributes(): void
    {
        $text = '[b]&alpha;[/b] &quot; &#39; &lt;img src=x onerror=alert(1)&gt;';
        $html = $this->getContainer()->get(Environment::class)->createTemplate(
            '<title>{{ name|bbcode2txt }}</title><div id="name" title="{{ name|bbcode2txt }}">{{ name|bbcode2txt }}</div>'
        )->render(['name' => $text]);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $element = $document->getElementById('name');
        $expected = 'α " \' <img src=x onerror=alert(1)>';

        self::assertNotNull($element);
        self::assertSame($expected, $document->title);
        self::assertSame($expected, $element->getAttribute('title'));
        self::assertSame($expected, $element->textContent);
        self::assertSame(2, $element->attributes->length);
        self::assertSame(0, $document->getElementsByTagName('img')->length);
    }

    public function testNavigationKeepsDecodedNamesInsideJavascriptStrings(): void
    {
        $dic = $this->getContainer();
        $twig = $dic->get(Environment::class);
        $name = '[b]&alpha;[/b] &#39;);alert(1);//&#92;&#10;&lt;img src=x onerror=alert(1)&gt;';

        foreach ([
            'onClickSpacecraft' => $dic->get(SpacecraftRepositoryInterface::class)->find(42),
            'onClickColony' => $dic->get(ColonyRepositoryInterface::class)->find(42)
        ] as $macro => $entity) {
            self::assertNotNull($entity);
            $originalName = $entity->getName();
            try {
                $entity->setName($name);
                $html = $twig->createTemplate('{% from "html/macros.twig" import ' . $macro . ' %}<a id="link" {{ ' . $macro . '(entity) }}>Name</a>')->render(['entity' => $entity]);
                $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
                $link = $document->getElementById('link');

                self::assertNotNull($link);
                self::assertSame(0, $document->getElementsByTagName('img')->length);
                self::assertSame(3, $link->attributes->length);
                $onclick = $link->getAttribute('onclick');
                self::assertStringContainsString('\\u03B1', $onclick);
                self::assertStringContainsString('\\u0026\\u0023039\\u003B', $onclick);
                self::assertStringContainsString('\\\\\\n', $onclick);
                self::assertStringNotContainsString("');alert(1)", $onclick);
                self::assertStringNotContainsString('<img', $onclick);
            } finally {
                $entity->setName($originalName);
            }
        }
    }

    public function testColonyTooltipKeepsEntitiesAsTextInItsEmbeddedHtml(): void
    {
        $dic = $this->getContainer();
        $colony = $dic->get(ColonyRepositoryInterface::class)->find(42);
        self::assertNotNull($colony);
        $originalName = $colony->getName();

        try {
            $colony->setName('[b]&alpha;[/b] &lt;img src=x onerror=alert(1)&gt;');
            $html = $dic->get(Environment::class)->render('html/game/component/coloniesComponent.twig', ['USER_COLONIES' => [$colony]]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $link = $document->getElementsByTagName('a')->item(0);
            self::assertNotNull($link);
            $tooltip = HTMLDocument::createFromString($link->getAttribute('data-tippy-content'), LIBXML_NOERROR);

            self::assertStringContainsString('Kolonieinformationen: α <img src=x onerror=alert(1)>', $tooltip->body->textContent);
            self::assertSame(0, $tooltip->getElementsByTagName('img')->length);
            self::assertSame(0, $tooltip->getElementsByTagName('script')->length);
        } finally {
            $colony->setName($originalName);
        }
    }

    public function testFlightEffectsRenderLineBreaksWithoutInterpretingFieldNamesAsHtml(): void
    {
        $twig = $this->getContainer()->get(Environment::class);
        $fieldType = new MapFieldType();
        $fieldType->setName('Crabs &alpha; <img src=x onerror=alert(1)>');

        foreach ([
            FieldTypeEffectEnum::LSS_MALFUNCTION,
            FieldTypeEffectEnum::NO_SPACECRAFT_COUNT,
            FieldTypeEffectEnum::DUBIOUS_SPACECRAFT_COUNT,
            FieldTypeEffectEnum::NO_SUBSPACE_LINES,
            FieldTypeEffectEnum::LSS_BLOCKADE,
            FieldTypeEffectEnum::HIT_CHANCE_INTERFERENCE,
            FieldTypeEffectEnum::EVADE_CHANCE_INTERFERENCE
        ] as $effect) {
            foreach (['html/systeminformation.twig', 'html/index/systeminformation.twig'] as $template) {
                $html = $twig->render($template, [
                    'INFORMATION' => ['[color=yellow]' . $effect->getFlightDestinationInfo($fieldType) . '[/color]'],
                    'TARGET_LINK' => null
                ]);
                $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
                $message = $document->getElementsByTagName('span')->item(0);

                self::assertNotNull($message);
                self::assertSame('color: yellow', $message->getAttribute('style'));
                self::assertSame(1, $message->getElementsByTagName('br')->length, $effect->name . ': ' . $template);
                self::assertSame(0, $message->getElementsByTagName('img')->length);
                self::assertStringContainsString('Crabs α <img src=x onerror=alert(1)>', $message->textContent);
                self::assertStringNotContainsString('<br', $message->textContent);
            }
        }
    }

    public function testRelationTextKeepsFormattingAndLineBreaks(): void
    {
        $source = new User();
        $source->setUsername('Quelle');
        $recipient = new User();
        $recipient->setUsername('Ziel');
        $relation = new Relation();
        $relation->setSourceUser($source);
        $relation->setRecipientUser($recipient);
        $relation->setText("[b]&alpha;[/b]\n&lt;script&gt;alert(1)&lt;/script&gt;");
        $html = $this->getContainer()->get(Environment::class)->render('html/alliance/showRelationText.twig', ['RELATION' => $relation]);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $message = $document->getElementsByTagName('strong')->item(0)?->parentNode;

        self::assertNotNull($message);
        self::assertSame(1, $message->getElementsByTagName('br')->length);
        self::assertSame(0, $message->getElementsByTagName('script')->length);
        self::assertStringContainsString('α', $message->textContent);
        self::assertStringContainsString('<script>alert(1)</script>', $message->textContent);
        self::assertStringNotContainsString('<br', $message->textContent);
    }
}
