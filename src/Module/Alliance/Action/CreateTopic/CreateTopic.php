<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateTopic;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class CreateTopic implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_CREATE_TOPIC';

    private CreateTopicRequestInterface $createTopicRequest;

    private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    public function __construct(
        CreateTopicRequestInterface $createTopicRequest,
        AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->createTopicRequest = $createTopicRequest;
        $this->allianceBoardPostRepository = $allianceBoardPostRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->allianceBoardRepository = $allianceBoardRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $user = $game->getUser();

        $name = $this->createTopicRequest->getTopicTitle();
        $text = $this->createTopicRequest->getText();

        if (mb_strlen($name) < 1) {
            $game->setView("SHOW_NEW_TOPIC");
            $game->addInformation(_('Es wurde kein Themenname eingegeben'));
            return;
        }

        if (mb_strlen($text) < 1) {
            $game->setView("SHOW_NEW_TOPIC");
            $game->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        /** @var AllianceBoardInterface $board */
        $board = $this->allianceBoardRepository->find($this->createTopicRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $date = time();

        $topic = $this->allianceBoardTopicRepository->prototype();
        $topic->setBoard($board);
        $topic->setAlliance($alliance);
        $topic->setName($name);
        $topic->setUser($user);
        $topic->setLastPostDate($date);

        $this->allianceBoardTopicRepository->save($topic);

        $board->getTopics()->add($topic);

        $post = $this->allianceBoardPostRepository->prototype();
        $post->setText($text);
        $post->setName($name);
        $post->setBoard($board);
        $post->setTopic($topic);
        $post->setUser($user);
        $post->setDate($date);

        $this->allianceBoardPostRepository->save($post);

        $topic->getPosts()->add($post);

        $game->setView(Board::VIEW_IDENTIFIER);

        $game->addInformation(_('Das Thema wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
