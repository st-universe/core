<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class DeleteBuildplan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_BUILDPLAN';

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private NPCLogRepositoryInterface $npcLogRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $buildplanId = request::postIntFatal('planid');
        if ($buildplanId == null) {
            $context->getInfo()->addInformation('Es wurde kein Bauplan ausgewählt');
            return;
        }

        $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
        if ($buildplan === null) {
            $context->getInfo()->addInformation('Der Bauplan konnte nicht gelöscht werden');
            return;
        }

        if ($buildplan->getUserId() !== $userId) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $buildplan->getUserId(),
                sprintf(
                    'Der Spieler %s hat deine Bauplan %s gelöscht',
                    $context->getUser()->getName(),
                    $buildplan->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }

        $crewCount = $buildplan->getCrew();


        $this->spacecraftBuildplanRepository->delete($buildplan);

        $logText = sprintf(
            '%s hat den Bauplan %s (%d) von Benutzer %s (%d) gelöscht. Crew: %d',
            $context->getUser()->getName(),
            $buildplan->getName(),
            $buildplan->getId(),
            $buildplan->getUser()->getName(),
            $buildplan->getUserId(),
            $crewCount
        );
        if ($context->getUser()->isNpc()) {
            $this->createLogEntry($logText, $userId);
        }

        $context->getInfo()->addInformation('Der Bauplan wurde gelöscht');
    }

    private function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
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
