<?php

declare(strict_types=1);

namespace Stu\Module\History\View\Overview;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private const MAX_LIMIT = 1000;

    private const LIMIT = 50;

    private const HISTORY_TYPE_DEFAULT = 1;

    private $possibleTypes = array(
        self::HISTORY_TYPE_DEFAULT => "Schiffe",
        2 => "Kolonie",
        3 => "Diplomatie",
        4 => "Sonstiges"
    );

    private $overviewRequest;

    private $historyRepository;

    public function __construct(
        OverviewRequestInterface $overviewRequest,
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->overviewRequest = $overviewRequest;
        $this->historyRepository = $historyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $type = $this->overviewRequest->getTypeId(array_keys($this->possibleTypes), self::HISTORY_TYPE_DEFAULT);
        $count = $this->overviewRequest->getCount(self::LIMIT);

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
            'HISTORY_TYPES',
            $history_types
        );
        $game->setTemplateVar(
            'HISTORY_COUNT',
            $count
        );
        $game->setTemplateVar(
            'HISTORY',
            $this->historyRepository->getByType($type, $count)
        );
    }
}