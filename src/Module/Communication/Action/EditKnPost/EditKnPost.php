<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use AccessViolation;
use RPGPlot;
use RPGPlotMember;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class EditKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_KN';

    public const EDIT_TIME = 600;

    private $editKnPostRequest;

    private $knPostRepository;

    public function __construct(
        EditKnPostRequestInterface $editKnPostRequest,
        KnPostRepositoryInterface $knPostRepository
    ) {
        $this->editKnPostRequest = $editKnPostRequest;
        $this->knPostRepository = $knPostRepository;
    }

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
        $plotid = $this->editKnPostRequest->getPlotId();

        if ($plotid > 0) {
            $plot = RPGPlot::getById($plotid);
            if ($plot && RPGPlotMember::mayWriteStory($plot->getId(), $userId)) {
                $post->setPlotId($plot->getId());
                $post->setTitle($plot->getTitle());
            }
        } else {
            if ($post->getPlotId() > 0) {
                $post->setPlotId(0);
            }
            $post->setTitle($title);
        }
        $post->setText($text);

        if (mb_strlen($text) < 10) {
            $game->addInformation(_('Der Text ist zu kurz'));
            return;
        }

        $post->setEditDate(time());

        $this->knPostRepository->save($post);

        $game->addInformation(_('Der Beitrag wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
