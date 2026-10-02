<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class SaveWelcomeMessage implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SAVE_WELCOME_MESSAGE';

    public function __construct(
        private FactionRepositoryInterface $factionRepository,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $user = $context->getUser();
        $factionId = $user->getFactionId();
        $welcomeMessage = request::postString('welcomemessage');


        if ($welcomeMessage === false) {
            $welcomeMessage = '';
        }

        $faction = $this->factionRepository->find($factionId);
        if ($faction === null) {
            throw new AccessViolationException();
        }

        $faction->setWelcomeMessage($welcomeMessage);

        $this->factionRepository->save($faction);

        if ($context->getUser()->isNpc()) {
            $this->createLogEntry($userId, $context->getUser()->getName(), $faction->getName());
        }

        $context->getInfo()->addInformation(_('Die Willkommensnachricht wurde gespeichert'));
    }

    private function createLogEntry(int $userId, string $userName, string $factionName): void
    {
        $logText = sprintf(
            '%s hat die Willkommensnachricht der Fraktion %s geändert.',
            $userName,
            $factionName
        );

        $entry = $this->npcLogRepository->prototype();
        $entry->setText($logText);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());
        $entry->setAdminView(false);

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
