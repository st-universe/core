<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnCharacter;
use Stu\Orm\Repository\KnCharacterRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;

final class EditKnPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_KN';

    public const int EDIT_TIME = 600;


    public function __construct(private EditKnPostRequestInterface $editKnPostRequest, private KnPostRepositoryInterface $knPostRepository, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private KnCharacterRepositoryInterface $knCharactersRepository, private UserCharacterRepositoryInterface $userCharactersRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = $this->knPostRepository->find($this->editKnPostRequest->getKnId());
        if ($post === null || ($post->getUserId() !== $userId && !$game->isAdmin())) {
            throw new AccessViolationException();
        }
        if ($post->getDate() < time() - self::EDIT_TIME && !$game->isAdmin()) {
            $game->getInfo()->addInformation(_('Dieser Beitrag kann nicht editiert werden'));
            return;
        }

        $plotId = $this->editKnPostRequest->getPlotId();

        $title = $this->editKnPostRequest->getTitle();
        if ($plotId === 0 && mb_strlen($title) < 6) {
            $game->getInfo()->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }
        if (mb_strlen($title) > 80) {
            $game->getInfo()->addInformation(_('Der Titel ist zu lang (maximal 80 Zeichen)'));
            return;
        }

        $text = $this->editKnPostRequest->getText();

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot !== null && $this->rpgPlotMemberRepository->getByPlotAndUser($plotId, $userId) !== null) {
                $post->setRpgPlot($plot);
            }
        } else {
            $post->setRpgPlot(null);
        }
        $post->setTitle($title);
        $post->setText($text);

        if (mb_strlen($text) < 10) {
            $game->getInfo()->addInformation(_('Der Text ist zu kurz'));
            return;
        }


        $currentCharacterEntities = $this->knCharactersRepository->findBy(['knPost' => $post]);
        $currentCharacterIds = array_map(fn (KnCharacter $character): int => $character->getUserCharacter()->getId(), $currentCharacterEntities);


        $newCharacterIdsInput = $this->editKnPostRequest->getCharacterIds();
        $newCharacterIds = array_filter(array_map('intval', explode(',', $newCharacterIdsInput)));

        $charactersToAdd = array_diff($newCharacterIds, $currentCharacterIds);
        $charactersToRemove = array_diff($currentCharacterIds, $newCharacterIds);

        $charactersAddedMapping = [];
        $charactersRemovedMapping = [];

        foreach ($charactersToAdd as $characterId) {
            $character = $this->userCharactersRepository->find($characterId);
            if (
                $character !== null
                && $character->getUser()->getId() !== $userId
                && $character->getUser()->getId() !== UserConstants::USER_NOONE
            ) {
                $ownerId = $character->getUser()->getId();
                $charactersAddedMapping[$ownerId][] = sprintf('%s (%d)', $character->getName(), $characterId);
            }
        }

        foreach ($charactersToRemove as $characterId) {
            $character = $this->userCharactersRepository->find($characterId);
            if (
                $character !== null
                && $character->getUser()->getId() !== $userId
                && $character->getUser()->getId() !== UserConstants::USER_NOONE
            ) {
                $ownerId = $character->getUser()->getId();
                $charactersRemovedMapping[$ownerId][] = sprintf('%s (%d)', $character->getName(), $characterId);
            }
        }


        foreach ($charactersAddedMapping as $ownerId => $characterNames) {

            $isSingleCharacter = count($characterNames) === 1;
            $charList = implode(', ', $characterNames);
            $text = sprintf(
                $isSingleCharacter
                    ? 'Dein Charakter %s wurde zu einem KN Post hinzugefügt. Titel des Posts: "%s".'
                    : 'Deine Charaktere %s wurden zu einem KN Post hinzugefügt. Titel des Posts: "%s".',
                $charList,
                $post->getTitle()
            );
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $ownerId,
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $post
            );
        }

        foreach ($charactersRemovedMapping as $ownerId => $characterNames) {
            $charList = implode(', ', $characterNames);
            $text = sprintf(
                'Deine Charaktere %s wurden aus einem KN Post entfernt. Titel des Posts: "%s"',
                $charList,
                $post->getTitle()
            );
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $ownerId,
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $post
            );
        }

        foreach ($currentCharacterIds as $currentCharacterId) {
            if (!in_array($currentCharacterId, $newCharacterIds)) {
                $entityToRemove = $this->knCharactersRepository->findOneBy([
                    'knPost' => $post->getId(),
                    'userCharacters' => $currentCharacterId
                ]);
                if ($entityToRemove !== null) {
                    $this->knCharactersRepository->delete($entityToRemove);
                }
            }
        }

        foreach ($newCharacterIds as $newCharacterId) {
            if (!in_array($newCharacterId, $currentCharacterIds)) {
                $userCharacter = $this->userCharactersRepository->find($newCharacterId);
                if (
                    $userCharacter !== null
                    && $userCharacter->getUser()->getId() !== UserConstants::USER_NOONE
                ) {
                    $newEntity = $this->knCharactersRepository->prototype();
                    $newEntity->setUserCharacter($userCharacter);
                    $newEntity->setKnPost($post);
                    $this->knCharactersRepository->save($newEntity);
                }
            }
        }


        $post->setEditDate(time());

        $this->knPostRepository->save($post);

        if ($game->isAdmin() && $game->getUser() != $post->getUser()) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $post->getUser()->getId(),
                sprintf(_('Der Beitrag "%s" mit der ID %d wurde von Admin %s bearbeitet'), $post->getTitle(), $post->getId(), $game->getUser()->getName()),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $post
            );
        }

        $game->getInfo()->addInformation(_('Der Beitrag wurde editiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
