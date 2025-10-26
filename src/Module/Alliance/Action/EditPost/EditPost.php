<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditPost;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;

final class EditPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_POSTING';

    public function __construct(private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $post = $this->allianceBoardPostRepository->find(request::postIntFatal('pid'));
        if ($post === null) {
            return;
        }

        if ($post->getBoard()->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $game->setView(Topic::VIEW_IDENTIFIER);

        $post->setText(request::postStringFatal('text'));
        $post->setEditDate(time());

        $this->allianceBoardPostRepository->save($post);

        $game->getInfo()->addInformation(_('Der Beitrag wurde editiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
