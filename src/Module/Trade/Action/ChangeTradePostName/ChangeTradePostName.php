<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\ChangeTradePostName;

use Stu\Exception\AccessViolationException;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ChangeTradePostName implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRADEPOST_CHANGE_NAME';

    public function __construct(private ChangeTradePostNameRequestInterface $changeTradePostNameRequest, private TradePostRepositoryInterface $tradePostRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $text = $this->changeTradePostNameRequest->getNewName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $newName = CleanTextUtils::clearEmojis($text);
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 200) {
            $context->getInfo()->addInformation(_('Der Name ist zu lang (Maximum: 200 Zeichen)'));
            return;
        }

        $tradepost = $this->tradePostRepository->find($this->changeTradePostNameRequest->getTradePostId());

        if ($tradepost === null || $tradepost->getUserId() !== $context->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $tradepost->setName($newName);

        $this->tradePostRepository->save($tradepost);

        $context->getInfo()->addInformation(_('Der Name des Handelsposten wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
