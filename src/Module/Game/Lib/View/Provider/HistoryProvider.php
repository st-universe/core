<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use request;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use JBBCode\Parser;

final class HistoryProvider implements ViewComponentProviderInterface
{
    private const int MAX_LIMIT = 10000;

    private const int LIMIT = 50;

    private HistoryRepositoryInterface $historyRepository;
    private Parser $bbcodeParser;

    public function __construct(HistoryRepositoryInterface $historyRepository, Parser $bbcodeParser)
    {
        $this->historyRepository = $historyRepository;
        $this->bbcodeParser = $bbcodeParser;
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
        $search = request::indString('hsearch') ?: '';

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
            $search
        );

        $historyEntries = $user->isShowPirateHistoryEntrys()
            ? $this->historyRepository->getByTypeAndSearch($type, $count)
            : $this->historyRepository->getByTypeAndSearchWithoutPirate($type, $count);

        $filteredEntries = array_filter($historyEntries, function ($entry) use ($search): bool {
            $this->bbcodeParser->parse($entry->getText());
            $plainText = $this->bbcodeParser->getAsText() ?: '';
            return stripos($plainText, $search) !== false;
        });

        $game->setTemplateVar(
            'HISTORY',
            $filteredEntries
        );
    }
}
