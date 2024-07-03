<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use request;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class HistoryProvider implements ViewComponentProviderInterface
{
    private const int MAX_LIMIT = 10000;

    private const int LIMIT = 50;

    public function __construct(private HistoryRepositoryInterface $historyRepository)
    {
    }

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $type = HistoryTypeEnum::tryFrom(request::indInt('htype')) ?? HistoryTypeEnum::SHIP;
        $count = request::indInt('hcount');
        if ($count === 0) {
            $count = self::LIMIT;
        }
        $search = request::indString('hsearch');

        if ($count < 1 || $count > self::MAX_LIMIT) {
            $count = self::MAX_LIMIT;
        }

        $history_types = [];
        foreach (HistoryTypeEnum::cases() as $enum) {
            $key = $enum->value;
            $history_types[$key]['type'] = $enum;
            $history_types[$key]['class'] = $enum == $type ? 'selected' : '';
            $history_types[$key]['count'] = $this->historyRepository->getAmountByType($key);
        }

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
            $search ?: ''
        );

        if ($user->isShowPirateHistoryEntrys()) {
            $game->setTemplateVar(
                'HISTORY',
                $this->historyRepository->getByTypeAndSearch($type, $count, $search)
            );
            return;
        } else {
            $game->setTemplateVar(
                'HISTORY',
                $this->historyRepository->getByTypeAndSearchWithoutPirate($type, $count, $search)
            );
            return;
        }
    }
}