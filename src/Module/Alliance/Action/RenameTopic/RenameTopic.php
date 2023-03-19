<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class RenameTopic implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_RENAME_TOPIC';

    private RenameTopicRequestInterface $renameTopicRequest;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    public function __construct(
        RenameTopicRequestInterface $renameTopicRequest,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->renameTopicRequest = $renameTopicRequest;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $name = $this->renameTopicRequest->getTitle();

        $topic = $this->allianceBoardTopicRepository->find($this->renameTopicRequest->getTopicId());
        if ($topic === null || $topic->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setView(Board::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $game->addInformation(_('Es wurde kein Themenname eingegeben'));
            return;
        }

        $topic->setName($name);
        $this->allianceBoardTopicRepository->save($topic);

        $game->addInformation(_('Das Thema wurde umbenannt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
