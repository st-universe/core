<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowEditor;

use AccessViolation;
use StarSystem;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowEditor implements ViewControllerInterface
{
    private const FIELDS_PER_SECTION = 20;
    public const VIEW_IDENTIFIER = 'SHOW_EDITOR';

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $game->setTemplateFile('html/mapeditor_overview.xhtml');
        $game->appendNavigationPart('starmap.php?SHOW_EDITOR=1', _('Karteneditor'));
        $game->setPageTitle(_('Karteneditor'));

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
        $game->setTemplateVar('SYSTEM_LIST', StarSystem::getList());
    }
}