<?php
declare(strict_types=1);

namespace Stu\Lib\Paging;

class PagingFactory
{
    public function createPaging(
        int $totalItems,
        int $itemsPerPage,
        int $mark,
        string $urlPattern = "",
        ?string $style = null
    ): Paging {
        $paging = new Paging($urlPattern, $style);

        if ($mark % $itemsPerPage !== 0 || $mark < 0) {
            $mark = 0;
        }

        $maxcount = $totalItems;
        $maxpage = (int)ceil($maxcount / $itemsPerPage);
        $curpage = (int)floor($mark / $itemsPerPage);
        $pages = [];
        if ($curpage != 0) {
            $pages[] = new Page($paging, "<<", 0, "pages");
            $pages[] = new Page($paging, "<", ($mark - $itemsPerPage), "pages");
        }

        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }

            $pages[] = new Page($paging, (string)$i, ($i * $itemsPerPage - $itemsPerPage), ($curpage + 1 === $i ? "pages selected" : "pages"));
        }

        if ($curpage + 1 !== $maxpage) {
            $pages[] = new Page($paging, ">", ($mark + $itemsPerPage), "pages");
            $pages[] = new Page($paging, ">>", $maxpage * $itemsPerPage - $itemsPerPage, "pages");
        }

        $paging->setPages($pages);
        return $paging;
    }
}
