<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class DeleteTopic implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_TOPIC';

    public function __construct(private DeleteTopicRequestInterface $deleteTopicRequest, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoardTopic $topic */
        $topic = $this->allianceBoardTopicRepository->find($this->deleteTopicRequest->getTopicId());
        if ($topic === null || $topic->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $this->allianceBoardTopicRepository->delete($topic);

        $game->addInformation(_('Das Thema wurde gelöscht'));

        $game->setView(Board::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
