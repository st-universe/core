<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\ChangeTradePostName;

use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ChangeTradePostName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRADEPOST_CHANGE_NAME';

    private ChangeTradePostNameRequestInterface $changeTradePostNameRequest;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        ChangeTradePostNameRequestInterface $changeTradePostNameRequest,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->changeTradePostNameRequest = $changeTradePostNameRequest;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $text = $this->changeTradePostNameRequest->getNewName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $newName = CleanTextUtils::clearEmojis($text);
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 200) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 200 Zeichen)'));
            return;
        }

        $tradepost = $this->tradePostRepository->find($this->changeTradePostNameRequest->getTradePostId());

        if ($tradepost === null || $tradepost->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $tradepost->setName($newName);

        $this->tradePostRepository->save($tradepost);

        $game->addInformation(_('Der Name des Handelsposten wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
