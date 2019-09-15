<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWritePm;

use PM;
use PMCategory;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_PM';

    private $showWritePmRequest;

    private $contactRepository;

    public function __construct(
        ShowWritePmRequestInterface $showWritePmRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->showWritePmRequest = $showWritePmRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $recipientId = $this->showWritePmRequest->getRecipientId();

        $pm = PM::getPMById($this->showWritePmRequest->getReplyPmId());
        if (!$pm || $pm->getRecipientId() != $userId) {
            $reply = null;
            $correspondence = null;
        } else {
            $reply = $pm;
            $correspondence = PM::getObjectsBy(
                sprintf(
                    'WHERE (send_user IN (%d,%d) OR recip_user IN (%d,%d)) AND cat_id IN (%s,%s) ORDER BY date DESC LIMIT 10',
                    $reply->getSenderId(),
                    $reply->getRecipientId(),
                    $reply->getSenderId(),
                    $reply->getRecipientId(),
                    PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, $reply->getRecipientId())->getId(),
                    PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, $reply->getSenderId())->getId()
                )
            );
        }

        $game->setTemplateFile('html/writepm.xhtml');
        $game->setPageTitle('Neue private Nachricht');
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            'Private Nachrichte verfassen'
        );

        $game->setTemplateVar(
            'RECIPIENT_ID',
            $recipientId === 0 ? '' : $recipientId
        );
        $game->setTemplateVar('REPLY', $reply);
        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($userId));
        $game->setTemplateVar('CORRESPONDENCE', $correspondence);
        $game->setTemplateVar('PM_CATEGORIES', PMCategory::getCategoryTree($userId));
    }
}
