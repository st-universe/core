<?php

declare(strict_types=1);

namespace Stu\Module\History\View\Overview;

use request;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private const MAX_LIMIT = 10000;

    private const LIMIT = 50;

    private array $possibleTypes = [
        HistoryTypeEnum::HISTORY_TYPE_SHIP => "Schiffe",
        HistoryTypeEnum::HISTORY_TYPE_STATION => "Station",
        HistoryTypeEnum::HISTORY_TYPE_COLONY => "Kolonie",
        HistoryTypeEnum::HISTORY_TYPE_ALLIANCE => "Diplomatie",
        HistoryTypeEnum::HISTORY_TYPE_OTHER => "Sonstiges"
    ];

    private OverviewRequestInterface $overviewRequest;

    private HistoryRepositoryInterface $historyRepository;

    public function __construct(
        OverviewRequestInterface $overviewRequest,
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->overviewRequest = $overviewRequest;
        $this->historyRepository = $historyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $type = $this->overviewRequest->getTypeId(array_keys($this->possibleTypes), HistoryTypeEnum::HISTORY_TYPE_SHIP);
        $count = $this->overviewRequest->getCount(self::LIMIT);
        $search = request::indString('hsearch');

        if ($count < 1 || $count > self::MAX_LIMIT) {
            $count = self::MAX_LIMIT;
        }

        $history_types = [];
        foreach ($this->possibleTypes as $key => $value) {
            $history_types[$key]['name'] = $value;
            $history_types[$key]['class'] = $key == $type ? 'selected' : '';
            $history_types[$key]['count'] = $this->historyRepository->getAmountByType($key);
        }

        $game->appendNavigationPart(
            'history.php',
            _('Ereignisse')
        );
        $game->setPageTitle(_('/ Ereignisse'));
        $game->setTemplateFile('html/history.xhtml');

        $game->setTemplateVar(
            'HISTORY_TYPE',
            $type
        );
        $game->setTemplateVar(
            'HISTORY_TYPE_COUNT',
            count($history_types)
        );
        $game->setTemplateVar(
            'HISTORY_TYPES',
            $history_types
        );
        $game->setTemplateVar(
            'HISTORY_COUNT',
            $count
        );
        $game->setTemplateVar(
            'HISTORY_SEARCH',
            $search ? $search : ''
        );
        $game->setTemplateVar(
            'HISTORY',
            $this->historyRepository->getByTypeAndSearch($type, $count, $search)
        );
    }
}
