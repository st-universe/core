<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopicInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class UnsetTopicSticky implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_UNSET_STICKY';

    private UnsetTopicStickyRequestInterface $unsetTopicStickyRequest;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    public function __construct(
        UnsetTopicStickyRequestInterface $unsetTopicStickyRequest,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->unsetTopicStickyRequest = $unsetTopicStickyRequest;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoardTopicInterface $topic */
        $topic = $this->allianceBoardTopicRepository->find($this->unsetTopicStickyRequest->getTopicId());
        if ($topic === null || $topic->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic->setSticky(false);

        $this->allianceBoardTopicRepository->save($topic);

        $game->addInformation(_('Die Markierung des Themas wurde entfernt'));

        $game->setView(Topic::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
