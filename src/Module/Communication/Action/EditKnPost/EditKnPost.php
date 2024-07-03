<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\KnCharactersRepositoryInterface;
use Stu\Orm\Repository\UserCharactersRepositoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnCharactersInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKn;

final class EditKnPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_KN';

    public const int EDIT_TIME = 600;


    public function __construct(private EditKnPostRequestInterface $editKnPostRequest, private KnPostRepositoryInterface $knPostRepository, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private KnCharactersRepositoryInterface $knCharactersRepository, private UserCharactersRepositoryInterface $userCharactersRepository, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->editKnPostRequest->getPostId());
        if ($post === null || $post->getUserId() !== $userId) {
            throw new AccessViolation();
        }
        if ($post->getDate() < time() - static::EDIT_TIME) {
            $game->addInformation(_('Dieser Beitrag kann nicht editiert werden'));
            return;
        }

        $title = $this->editKnPostRequest->getTitle();
        $text = $this->editKnPostRequest->getText();
        $plotId = $this->editKnPostRequest->getPlotId();

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
            $game->addInformation(_('Der Text ist zu kurz'));
            return;
        }


        $href = sprintf('comm.php?%s=1&id=%d', ShowSingleKn::VIEW_IDENTIFIER, $post->getId());
        $currentCharacterEntities = $this->knCharactersRepository->findBy(['knPost' => $post]);
        $currentCharacterIds = array_map(function (KnCharactersInterface $character): int {
            return $character->getUserCharacters()->getId();
        }, $currentCharacterEntities);


        $newCharacterIdsInput = $this->editKnPostRequest->getCharacterIds();
        $newCharacterIds = array_filter(array_map('intval', explode(',', $newCharacterIdsInput)));

        $charactersToAdd = array_diff($newCharacterIds, $currentCharacterIds);
        $charactersToRemove = array_diff($currentCharacterIds, $newCharacterIds);

        $charactersAddedMapping = [];
        $charactersRemovedMapping = [];

        foreach ($charactersToAdd as $characterId) {
            $character = $this->userCharactersRepository->find($characterId);
            if ($character !== null && $character->getUser()->getId() !== $userId) {
                $ownerId = $character->getUser()->getId();
                $charactersAddedMapping[$ownerId][] = sprintf('%s (%d)', $character->getName(), $characterId);
            }
        }

        foreach ($charactersToRemove as $characterId) {
            $character = $this->userCharactersRepository->find($characterId);
            if ($character !== null && $character->getUser()->getId() !== $userId) {
                $ownerId = $character->getUser()->getId();
                $charactersRemovedMapping[$ownerId][] = sprintf('%s (%d)', $character->getName(), $characterId);
            }
        }


        foreach ($charactersAddedMapping as $ownerId => $characterNames) {
            $charList = implode(', ', $characterNames);
            $text = sprintf(
                'Deine Charaktere %s wurden zu einem KN Post hinzugefügt. Titel des Posts: "%s"',
                $charList,
                $post->getTitle()
            );
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $ownerId,
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $href
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
                UserEnum::USER_NOONE,
                $ownerId,
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $href
            );
        }

        foreach ($currentCharacterIds as $currentCharacterId) {
            if (!in_array($currentCharacterId, $newCharacterIds)) {
                $entityToRemove = $this->knCharactersRepository->findOneBy([
                    'knPost' => $post->getId(),
                    'userCharacters' => $currentCharacterId
                ]);
                if ($entityToRemove) {
                    $this->knCharactersRepository->delete($entityToRemove);
                }
            }
        }

        foreach ($newCharacterIds as $newCharacterId) {
            if (!in_array($newCharacterId, $currentCharacterIds)) {
                $userCharacter = $this->userCharactersRepository->find($newCharacterId);
                if ($userCharacter) {
                    $newEntity = $this->knCharactersRepository->prototype();
                    $newEntity->setUserCharacters($userCharacter);
                    $newEntity->setKnPost($post);
                    $this->knCharactersRepository->save($newEntity);
                }
            }
        }


        $post->setEditDate(time());

        $this->knPostRepository->save($post);

        $game->addInformation(_('Der Beitrag wurde editiert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
