<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EditKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_KN';

    public const EDIT_TIME = 600;

    private EditKnPostRequestInterface $editKnPostRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    public function __construct(
        EditKnPostRequestInterface $editKnPostRequest,
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->editKnPostRequest = $editKnPostRequest;
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
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

        $post->setEditDate(time());

        $this->knPostRepository->save($post);

        $game->addInformation(_('Der Beitrag wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
