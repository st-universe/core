<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use UserYRow;

final class ShowSection implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTION';
    private const FIELDS_PER_SECTION = 20;

    private ShowSectionRequestInterface $showSectionRequest;

    public function __construct(
        ShowSectionRequestInterface $showSectionRequest
    ) {
        $this->showSectionRequest = $showSectionRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $xCoordinate = $this->showSectionRequest->getXCoordinate();
        $yCoordinate = $this->showSectionRequest->getYCoordinate();
        $section_id = $this->showSectionRequest->getSectionId();

        $maxx = $xCoordinate * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        $maxy = $yCoordinate * self::FIELDS_PER_SECTION;
        $miny = $maxy - self::FIELDS_PER_SECTION + 1;

        $fields = [];
        foreach (range($miny, $maxy) as $value) {
            $fields[] = new UserYRow($game->getUser(), $value, $minx, $maxx);
        }

        $game->setTemplateFile('html/starmap_section.xhtml');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));
        $game->appendNavigationPart(
            sprintf(
                'starmap.php?SHOW_SECTION=1&x=%d&y=%d&sec=%d',
                $xCoordinate,
                $yCoordinate,
                $section_id
            ),
            sprintf(_('Sektion %d anzeigen'), $section_id)
        );
        $game->setPageTitle(_('Sektion anzeigen'));
        $game->setTemplateVar('SECTION_ID', $section_id);
        $game->setTemplateVar('HEAD_ROW', range($minx, $maxx));
        $game->setTemplateVar('MAP_FIELDS', $fields);
        $game->setTemplateVar('HAS_NAV_LEFT', $xCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_RIGHT', $xCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_X);
        $game->setTemplateVar('HAS_NAV_UP', $yCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_DOWN', $yCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_Y);
        $game->setTemplateVar(
            'NAV_UP',
            sprintf(
                '?%s=1&x=%d&y=%d&sec=%d',
                static::VIEW_IDENTIFIER,
                $xCoordinate,
                $yCoordinate > 1 ? $yCoordinate - 1 : 1,
                $section_id - 6
            )
        );
        $game->setTemplateVar(
            'NAV_DOWN',
            sprintf(
                "?%s=1&x=%d&y=%d&sec=%d",
                static::VIEW_IDENTIFIER,
                $xCoordinate,
                $yCoordinate + 1 > MapEnum::MAP_MAX_Y / self::FIELDS_PER_SECTION ? $yCoordinate : $yCoordinate + 1,
                $section_id + 6
            )
        );
        $game->setTemplateVar(
            'NAV_LEFT',
            sprintf(
                "?%s=1&x=%d&y=%d&sec=%d",
                static::VIEW_IDENTIFIER,
                $xCoordinate > 1 ? $xCoordinate - 1 : 1,
                $yCoordinate,
                $section_id - 1
            )
        );
        $game->setTemplateVar(
            'NAV_RIGHT',
            sprintf(
                '?%s=1&x=%d&y=%d&sec=%d',
                static::VIEW_IDENTIFIER,
                $xCoordinate + 1 > MapEnum::MAP_MAX_X / self::FIELDS_PER_SECTION ? $xCoordinate : $xCoordinate + 1,
                $yCoordinate,
                $section_id + 1
            )
        );
    }
}
