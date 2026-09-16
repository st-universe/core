<?php

declare(strict_types=1);

namespace Stu\Lib\Paging;

use Stu\StuTestCase;

class PagingTest extends StuTestCase
{
    public function testPageUsesPagingUrlPatternAndStoresMetaData(): void
    {
        $paging = new Paging('/game.php?action=view', 'compact');
        $page = new Page($paging, '5', 80, 'pages selected');

        self::assertSame('5', $page->getLabel());
        self::assertSame('pages selected', $page->getCssClass());
        self::assertSame('/game.php?action=view&mark=80', $page->getUrl());
    }

    public function testPagingStoresPagesAndUsesDefaultPatternWhenUrlPatternIsEmpty(): void
    {
        $paging = new Paging('', 'primary');
        $pages = [new Page($paging, '1', 0, 'pages selected')];

        $paging->setPages($pages);

        self::assertSame($pages, $paging->getPages());
        self::assertSame('?mark=%d', $paging->getUrlPattern());
        self::assertSame('primary', $paging->getStyle());

        $nullStylePaging = new Paging('', null);
        self::assertNull($nullStylePaging->getStyle());
    }

    public function testCreatePagingResetsInvalidMarkAndBuildsFirstPageNavigation(): void
    {
        $paging = (new PagingFactory())->createPaging(250, 25, 37);

        self::assertSame('?mark=%d', $paging->getUrlPattern());

        $labels = array_map(static fn (Page $page): string => $page->getLabel(), $paging->getPages());
        self::assertSame(['1', '2', '3', '>', '>>'], $labels);

        self::assertSame('pages selected', $paging->getPages()[0]->getCssClass());
        self::assertSame('?mark=0', $paging->getPages()[0]->getUrl());
        self::assertSame('?mark=25', $paging->getPages()[1]->getUrl());
        self::assertSame('?mark=225', $paging->getPages()[4]->getUrl());
    }

    public function testCreatePagingAddsPreviousAndNextLinksForMiddlePage(): void
    {
        $paging = (new PagingFactory())->createPaging(100, 25, 25, '/view.php?mode=all', 'nav');

        $labels = array_map(static fn (Page $page): string => $page->getLabel(), $paging->getPages());
        self::assertSame(['<<', '<', '1', '2', '3', '4', '>', '>>'], $labels);

        self::assertSame('/view.php?mode=all&mark=0', $paging->getPages()[0]->getUrl());
        self::assertSame('/view.php?mode=all&mark=0', $paging->getPages()[1]->getUrl());
        self::assertSame('/view.php?mode=all&mark=50', $paging->getPages()[6]->getUrl());
        self::assertSame('pages selected', $paging->getPages()[3]->getCssClass());
        self::assertSame('nav', $paging->getStyle());
    }

    public function testCreatePagingOmitsNextLinksOnLastPage(): void
    {
        $paging = (new PagingFactory())->createPaging(100, 25, 75, '/final.php');

        $labels = array_map(static fn (Page $page): string => $page->getLabel(), $paging->getPages());
        self::assertSame(['<<', '<', '2', '3', '4'], $labels);

        self::assertSame('/final.php&mark=0', $paging->getPages()[0]->getUrl());
        self::assertSame('/final.php&mark=50', $paging->getPages()[1]->getUrl());
        self::assertSame('/final.php&mark=75', $paging->getPages()[4]->getUrl());
        self::assertSame('pages selected', $paging->getPages()[4]->getCssClass());
    }
}
