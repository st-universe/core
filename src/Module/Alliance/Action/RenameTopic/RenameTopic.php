<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class RenameTopic implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RENAME_TOPIC';

    public function __construct(
        private RenameTopicRequestInterface $renameTopicRequest,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $name = $this->renameTopicRequest->getTitle();

        $topic = $this->allianceBoardTopicRepository->find($this->renameTopicRequest->getTopicId());
        if ($topic === null || $topic->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $game->setView(Board::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $game->getInfo()->addInformation(_('Es wurde kein Themenname eingegeben'));
            return;
        }

        $topic->setName($name);
        $this->allianceBoardTopicRepository->save($topic);

        $game->getInfo()->addInformation(_('Das Thema wurde umbenannt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
