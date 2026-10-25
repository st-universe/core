<?php

declare(strict_types=1);

namespace Stu\Html\Communication;

use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\TwigTestCase;
use Twig\Environment;

class PlotDetailsTemplateTest extends TwigTestCase
{
    #[DataProvider('plotAccess')]
    public function testPlotControlsAndEditDateAreVisible(bool $owned, bool $active): void
    {
        $container = $this->getContainer();
        $plot = $container->get(RpgPlotRepositoryInterface::class)->find(9);
        self::assertNotNull($plot);
        $originalEndDate = $plot->getEndDate();
        $originalEditDate = $plot->getEditDate();
        $plot->setEndDate($active ? null : 1732214228);
        $plot->setEditDate(1732214228);

        try {
            $html = $container->get(Environment::class)->render('html/communication/plotdetails.twig', [
                'PLOT' => $plot,
                'MAY_EDIT' => $owned,
                'KN_POSTINGS' => [],
                'SESSIONSTRING' => 'owner-token'
            ]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

            self::assertCount($owned ? 1 : 0, $document->querySelectorAll('input[type="submit"][name="SHOW_EDIT_PLOT"][form="edit-plot-form"]'));
            if ($owned) {
                self::assertFalse($document->querySelector('[name="SHOW_EDIT_PLOT"]')->hasAttribute('onclick'));
                self::assertCount(0, $document->querySelectorAll('#edit-plot-form [name="SHOW_PLOT"]'));
            }
            self::assertCount($owned && !$active ? 1 : 0, $document->querySelectorAll('[name="B_REOPEN_PLOT"]'));
            self::assertCount($owned && $active ? 1 : 0, $document->querySelectorAll('[name="B_END_PLOT"]'));
            self::assertStringContainsString('Bearbeitet: 21.07.2394 11:30', $html);
            self::assertSame('post', $document->querySelector('form')->getAttribute('method'));
            self::assertSame('owner-token', $document->querySelector('[name="sstr"]')->getAttribute('value'));
        } finally {
            $plot->setEndDate($originalEndDate);
            $plot->setEditDate($originalEditDate);
        }
    }

    public static function plotAccess(): iterable
    {
        yield 'owner active plot' => [true, true];
        yield 'owner ended plot' => [true, false];
        yield 'visitor active plot' => [false, true];
        yield 'visitor ended plot' => [false, false];
    }
}
