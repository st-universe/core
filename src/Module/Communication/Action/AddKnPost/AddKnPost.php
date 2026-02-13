<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use Stu\Module\Communication\Lib\NewKnPostNotificatorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Repository\KnCharacterRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddKnPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_WRITE_KN';

    public function __construct(
        private AddKnPostRequestInterface $addKnPostRequest,
        private KnPostRepositoryInterface $knPostRepository,
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private RpgPlotRepositoryInterface $rpgPlotRepository,
        private UserRepositoryInterface $userRepository,
        private NewKnPostNotificatorInterface $newKnPostNotificator,
        private KnCharacterRepositoryInterface $knCharactersRepository,
        private UserCharacterRepositoryInterface $userCharactersRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $plot = null;

        $title = $this->addKnPostRequest->getTitle();
        $text = $this->addKnPostRequest->getText();
        $plotId = $this->addKnPostRequest->getPlotId();
        $mark = $this->addKnPostRequest->getPostMark();

        if (mb_strlen($text) < 50) {
            $game->getInfo()->addInformation(_('Der Text ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }

        $post = $this->knPostRepository->prototype();

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot !== null && $this->rpgPlotMemberRepository->getByPlotAndUser($plotId, $userId) !== null) {
                $post->setRpgPlot($plot);
            }
        } else {
            if (mb_strlen($title) < 6) {
                $game->getInfo()->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
                return;
            }

            if (mb_strlen($title) > 80) {
                $game->getInfo()->addInformation(_('Der Titel ist zu lang (maximal 80 Zeichen)'));
                return;
            }
        }


        $post->setTitle($title);
        $post->setText($text);
        $post->setUser($user);
        $post->setUsername($user->getName());
        $post->setdelUserId($userId);
        $post->setDate(time());

        $this->knPostRepository->save($post);


        $characterIdsInput = $this->addKnPostRequest->getCharacterIds();
        $idsRaw = explode(',', $characterIdsInput);
        $validIds = [];
        $addedCharacterIds = [];

        foreach ($idsRaw as $idRaw) {
            $idTrimmed = trim($idRaw);
            if (is_numeric($idTrimmed)) {
                $validIds[] = (int)$idTrimmed;
            }
        }

        foreach ($validIds as $id) {
            $userCharacter = $this->userCharactersRepository->find($id);
            if ($userCharacter === null) {
                $game->getInfo()->addInformation(_("Kein Character mit der ID $id gefunden."));
                continue;
            }
            if ($userCharacter->getUser()->getId() === UserConstants::USER_NOONE) {
                continue;
            }

            $character = $this->knCharactersRepository->prototype();
            $character->setUserCharacter($userCharacter);
            $character->setKnPost($post);
            $this->knCharactersRepository->save($character);
            $post->getKnCharacters()->add($character);
            $addedCharacterIds[] = $id;
        }
        $this->notifyCharacterOwners($post, $addedCharacterIds);

        if ($plot !== null) {
            $this->newKnPostNotificator->notify($post, $plot);
        }

        $game->getInfo()->addInformation(_('Der Beitrag wurde hinzugefügt'));

        if ($mark !== 0) {
            $user->setKNMark($post->getId());

            $this->userRepository->save($user);
        }

        $game->setView(GameController::DEFAULT_VIEW);
    }

    /**
     * @param int[] $characterIds
     */
    private function notifyCharacterOwners(KnPost $post, array $characterIds): void
    {
        $userCharactersMap = [];

        foreach ($characterIds as $characterId) {
            $character = $this->userCharactersRepository->find($characterId);
            if ($character !== null) {
                $ownerId = $character->getUser()->getId();
                if ($ownerId === UserConstants::USER_NOONE) {
                    continue;
                }

                $characterNameWithId = sprintf('%s (%d)', $character->getName(), $characterId);
                if (!array_key_exists($ownerId, $userCharactersMap)) {
                    $userCharactersMap[$ownerId] = [];
                }
                $userCharactersMap[$ownerId][] = $characterNameWithId;
            }
        }

        foreach ($userCharactersMap as $ownerId => $characterNamesWithIds) {
            if ($ownerId !== $post->getUser()->getId()) {

                $isSingleCharacter = count($characterNamesWithIds) === 1;
                $charList = implode(', ', $characterNamesWithIds);
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
        }
    }




    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
