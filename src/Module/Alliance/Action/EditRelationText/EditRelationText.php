<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditRelationText;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class EditRelationText implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_RELATION_TEXT';

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    public function __construct(
        private RelationRepositoryInterface $allianceRelationRepository,
        private EditRelationTextRequestInterface $editRelationTextRequest,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRelationManagerInterface $userRelationManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $relationId = $this->editRelationTextRequest->getRelationId();
        $text = $this->editRelationTextRequest->getText();

        $relation = $this->allianceRelationRepository->find($relationId);

        if ($relation === null) {
            return;
        }

        if (!$this->userRelationManager->canEditRelationContract($user, $relation)) {
            throw new AccessViolationException();
        }

        $relation->setText($text);
        $relation->setLastEdited(time());

        $this->allianceRelationRepository->save($relation);

        $this->sendNotification($relation->getSourceParty(), $relation, $user);
        $this->sendNotification($relation->getRecipientParty(), $relation, $user);

        $game->getInfo()->addInformation('Der Vertragstext wurde erfolgreich bearbeitet');
    }

    private function sendNotification(
        Alliance|User $party,
        Relation $relation,
        User $editor
    ): void {
        $relationTypeName = $relation->getType()->getDescription();
        $message = sprintf(
            'Der Vertragstext für das %s zwischen [b]%s[/b] und [b]%s[/b] wurde von [b]%s[/b] (%d) bearbeitet.',
            $relationTypeName,
            $relation->getSourceParty()->getName(),
            $relation->getRecipientParty()->getName(),
            $editor->getName(),
            $editor->getId()
        );

        if ($party instanceof User) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $party->getId(),
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );

            return;
        }

        $founderJob = $party->getFounder();
        foreach ($founderJob->getUsers() as $user) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $user->getId(),
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }

        $successorJob = $party->getSuccessor();
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

        $diplomaticJob = $party->getDiplomatic();
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
