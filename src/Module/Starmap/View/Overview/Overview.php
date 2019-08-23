<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\Overview;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    private const FIELDS_PER_SECTION = 20;

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Sternenkarte'));
        $game->setTemplateFile('html/starmap.xhtml');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));

        $xHeadRow = [];
        for ($j = 1; $j <= MAP_MAX_X / static::FIELDS_PER_SECTION; $j++) {
            $xHeadRow[] = $j;
        }

        $sections = [];
        $k = 1;
        for ($i = 1; $i <= MAP_MAX_Y / self::FIELDS_PER_SECTION; $i++) {
            for ($j = 1; $j <= MAP_MAX_X / self::FIELDS_PER_SECTION; $j++) {
                $sections[$i][$j] = $k;
                $k++;
            }
        }

        $game->setTemplateVar('X_HEAD_ROW', $xHeadRow);
        $game->setTemplateVar('SECTIONS', $sections);
        $game->setTemplateVar('FIELDS_PER_SECTION', static::FIELDS_PER_SECTION);
    }
}