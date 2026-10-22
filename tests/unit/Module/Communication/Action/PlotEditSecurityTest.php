<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action;

use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlot;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlotRequestInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\ProtectedMutationTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class PlotEditSecurityTest extends ProtectedMutationTestCase
{
    public function testEditRejectsForgedRequests(): void
    {
        $this->assertRequestProtection(new EditKnPlot($this->mock(EditKnPlotRequestInterface::class), $this->mock(RpgPlotRepositoryInterface::class)));
    }

    #[DataProvider('ownership')]
    public function testOnlyOwnerCanEdit(bool $owned): void
    {
        $request = $this->mock(EditKnPlotRequestInterface::class);
        $request->shouldReceive('getPlotId')->once()->andReturn(7);
        $plots = $this->mock(RpgPlotRepositoryInterface::class);
        $plot = $this->mock(RpgPlot::class);
        $plots->shouldReceive('find')->with(7)->once()->andReturn($plot);
        $plot->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(101);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->once()->andReturn($user);
        if ($owned) {
            $request->shouldReceive('getTitle')->once()->andReturn('Geänderter Titel');
            $request->shouldReceive('getText')->once()->andReturn('Neue Beschreibung');
            $plot->shouldReceive('setTitle')->with('Geänderter Titel')->once();
            $plot->shouldReceive('setDescription')->with('Neue Beschreibung')->once();
            $plots->shouldReceive('save')->with($plot)->once();
            $info = $this->mock(InformationWrapper::class);
            $info->shouldReceive('addInformation')->once();
            $context->shouldReceive('getInfo')->once()->andReturn($info);
            $context->shouldReceive('setView')->once();
        }
        new EditKnPlot($request, $plots)->handle($context);
    }

    public function testEditorEscapesStoredAttributeAndTextareaPayloads(): void
    {
        $title = 'Titel" autofocus onfocus="alert(1)';
        $description = '</textarea><script>alert(2)</script>';
        $plot = $this->mock(RpgPlot::class);
        $plot->shouldReceive('getId')->andReturn(7);
        $plot->shouldReceive('getTitle')->andReturn($title);
        $plot->shouldReceive('getDescription')->andReturn($description);
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../../src'), ['autoescape' => 'html']);
        $html = $twig->render('html/communication/plot/editPlot.twig', ['PLOT' => $plot, 'SESSIONSTRING' => 'owner-token', 'javascriptPath' => '/static']);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $input = $document->querySelector('input[name="title"]');
        self::assertSame($title, $input->getAttribute('value'));
        self::assertFalse($input->hasAttribute('autofocus'));
        self::assertFalse($input->hasAttribute('onfocus'));
        self::assertSame($description, $document->querySelector('textarea')->textContent);
        self::assertCount(1, $document->querySelectorAll('script'));
        self::assertSame('owner-token', $document->querySelector('[name="sstr"]')->getAttribute('value'));
        self::assertSame('post', $document->querySelector('form')->getAttribute('method'));
    }

    public static function ownership(): iterable
    {
        yield 'own' => [true];
        yield 'foreign' => [false];
    }
}
