<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Mockery\MockInterface;
use Stu\Module\Twig\TwigPage;
use Stu\Module\Twig\TwigPageInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

class TwigPageTest extends StuTestCase
{
    /**
     * @var Environment|MockInterface
     */
    private $environment;


    private TwigPageInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->environment = $this->mock(Environment::class);

        $this->subject = new TwigPage(
            $this->environment
        );
    }

    public function testIsTemplateSet(): void
    {
        $this->assertFalse($this->subject->isTemplateSet());
        $this->subject->setTemplate('template');
        $this->assertTrue($this->subject->isTemplateSet());
    }

    public function testRenderWithGlobalEnvironmentVariable(): void
    {
        $template = $this->mock(Template::class);
        $templateWrapper = new TemplateWrapper($this->environment, $template);

        $template->shouldReceive('render')
            ->with([])
            ->andReturn('rendered');

        $this->environment->shouldReceive('addGlobal')
            ->with('key', 'value');
        $this->environment->shouldReceive('load')
            ->with('template')
            ->andReturn($templateWrapper);

        $this->subject->setVar('key', 'value', true);
        $this->subject->setTemplate('template');

        $result = $this->subject->render();

        $this->assertEquals('rendered', $result);
    }

    public function testRenderWithContextVariable(): void
    {
        $template = $this->mock(Template::class);
        $templateWrapper = new TemplateWrapper($this->environment, $template);

        $template->shouldReceive('render')
            ->with(['key' => 'value'])
            ->andReturn('rendered');

        $this->environment->shouldReceive('load')
            ->with('template')
            ->andReturn($templateWrapper);

        $this->subject->setVar('key', 'value');
        $this->subject->setTemplate('template');

        $result = $this->subject->render();

        $this->assertEquals('rendered', $result);
    }
}
