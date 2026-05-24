<?php

declare(strict_types=1);

namespace Stu\Html\Communication;

use Stu\TwigTestCase;
use Twig\Environment;

class KnCommentsTemplateTest extends TwigTestCase
{
    public function testInitialPopupWrapsCommentsForAjaxRefresh(): void
    {
        $html = $this->renderKnComments(true);

        $this->assertStringContainsString('id="kncomments"', $html);
        $this->assertStringContainsString('postComment(42)', $html);
    }

    public function testAjaxRefreshDoesNotWrapCommentsAgain(): void
    {
        $html = $this->renderKnComments(false);

        $this->assertStringNotContainsString('id="kncomments"', $html);
        $this->assertStringContainsString('postComment(42)', $html);
    }

    private function renderKnComments(bool $wrap): string
    {
        $template = $this->getContainer()->get(Environment::class)->load('html/communication/knComments.twig');

        return $template->render([
            'POST' => new class {
                public function getId(): int
                {
                    return 42;
                }
            },
            'COMMENTS' => [],
            'CHARLIMIT' => 250,
            'WRAP_KN_COMMENTS' => $wrap,
        ]);
    }
}
