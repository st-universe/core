<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use AccessViolation;
use KNPosting;
use RPGPlot;
use RPGPlotMember;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class EditKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_KN';

    private $editKnPostRequest;

    public function __construct(
        EditKnPostRequestInterface $editKnPostRequest
    ) {
        $this->editKnPostRequest = $editKnPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = new KNPosting($this->editKnPostRequest->getPostId());
        if ($post->getUserId() != $userId) {
            throw new AccessViolation();
        }
        if (!$post->isEditAble()) {
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
            if ($post->hasPlot()) {
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
        $post->save();

        $game->addInformation(_('Der Beitrag wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
