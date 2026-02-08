<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditRelationText;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class EditRelationText implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_RELATION_TEXT';

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private EditRelationTextRequestInterface $editRelationTextRequest,
        private PrivateMessageSenderInterface $privateMessageSender,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS)) {
            throw new AccessViolationException();
        }

        $relationId = $this->editRelationTextRequest->getRelationId();
        $text = $this->editRelationTextRequest->getText();

        $relation = $this->allianceRelationRepository->find($relationId);

        if ($relation === null) {
            return;
        }

        if ($relation->getAlliance() !== $alliance && $relation->getOpponent() !== $alliance) {
            throw new AccessViolationException();
        }

        $oldText = $relation->getText();
        $relation->setText($text);
        $relation->setLastEdited(time());

        $this->allianceRelationRepository->save($relation);

        // Benachrichtigung an beide Allianzen senden
        $allianceA = $relation->getAlliance();
        $allianceB = $relation->getOpponent();

        $this->sendNotificationToLeaders($allianceA, $relation, $user->getName(), $user->getId(), $oldText, $text);
        if ($allianceA->getId() !== $allianceB->getId()) {
            $this->sendNotificationToLeaders($allianceB, $relation, $user->getName(), $user->getId(), $oldText, $text);
        }

        $game->getInfo()->addInformation('Der Vertragstext wurde erfolgreich bearbeitet');
    }

    private function sendNotificationToLeaders(Alliance $alliance, AllianceRelation $relation, string $editorName, int $editorId, ?string $oldText, ?string $newText): void
    {
        $relationTypeName = $relation->getType()->getDescription();
        $allianceAName = $relation->getAlliance()->getName();
        $allianceBName = $relation->getOpponent()->getName();

        $message = sprintf(
            "Der Vertragstext für das %s zwischen [b]%s[/b] und [b]%s[/b] wurde von [b]%s[/b] (%d) bearbeitet.",
            $relationTypeName,
            $allianceAName,
            $allianceBName,
            $editorName,
            $editorId
        );

        $founderJob = $alliance->getFounder();
        foreach ($founderJob->getUsers() as $user) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $user->getId(),
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }

        $successorJob = $alliance->getSuccessor();
        if ($successorJob !== null) {
            foreach ($successorJob->getUsers() as $user) {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $user->getId(),
                    $message,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
                );
            }
        }

        $diplomaticJob = $alliance->getDiplomatic();
        if ($diplomaticJob !== null) {
            foreach ($diplomaticJob->getUsers() as $user) {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $user->getId(),
                    $message,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
                );
            }
        }
    }
}
