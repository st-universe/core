<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class ShowMapEditor implements ViewControllerInterface
{
    private const FIELDS_PER_SECTION = 20;
    public const VIEW_IDENTIFIER = 'SHOW_MAP_EDITOR';

    private StarSystemRepositoryInterface $starSystemRepository;

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository
    ) {
        $this->starSystemRepository = $starSystemRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/mapeditor_overview.xhtml');
        $game->appendNavigationPart('/admin/?SHOW_MAP_EDITOR=1', _('Karteneditor'));
        $game->setPageTitle(_('Karteneditor'));

        $xHeadRow = [];
        for ($j = 1; $j <= MapEnum::MAP_MAX_X / static::FIELDS_PER_SECTION; $j++) {
            $xHeadRow[] = $j;
        }

        $sections = [];
        $k = 1;
        for ($i = 1; $i <= MapEnum::MAP_MAX_Y / self::FIELDS_PER_SECTION; $i++) {
            for ($j = 1; $j <= MapEnum::MAP_MAX_X / self::FIELDS_PER_SECTION; $j++) {
                $sections[$i][$j] = $k;
                $k++;
            }
        }

        $game->setTemplateVar('X_HEAD_ROW', $xHeadRow);
        $game->setTemplateVar('SECTIONS', $sections);
        $game->setTemplateVar('FIELDS_PER_SECTION', static::FIELDS_PER_SECTION);
        $game->setTemplateVar('SYSTEM_LIST', $this->starSystemRepository->findAll());
    }
}
