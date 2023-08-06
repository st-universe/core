<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use Stu\Module\Communication\Lib\NewKnPostNotificatorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_KN';

    private AddKnPostRequestInterface $addKnPostRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private UserRepositoryInterface $userRepository;

    private NewKnPostNotificatorInterface $newKnPostNotificator;

    public function __construct(
        AddKnPostRequestInterface $addKnPostRequest,
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        UserRepositoryInterface $userRepository,
        NewKnPostNotificatorInterface $newKnPostNotificator
    ) {
        $this->addKnPostRequest = $addKnPostRequest;
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->userRepository = $userRepository;
        $this->newKnPostNotificator = $newKnPostNotificator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $plot = null;

        $title = $this->addKnPostRequest->getTitle();
        $text = $this->addKnPostRequest->getText();
        $plotId = $this->addKnPostRequest->getPlotId();
        $mark = $this->addKnPostRequest->getPostMark();

        if (mb_strlen($text) < 50) {
            $game->addInformation(_('Der Text ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }

        $post = $this->knPostRepository->prototype();

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot !== null && $this->rpgPlotMemberRepository->getByPlotAndUser($plotId, $userId) !== null) {
                $post->setRpgPlot($plot);
            }
        } else {
            if (mb_strlen($title) < 6) {
                $game->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
                return;
            }

            if (mb_strlen($title) > 80) {
                $game->addInformation(_('Der Titel ist zu lang (maximal 80 Zeichen)'));
                return;
            }
        }
        $post->setTitle($title);
        $post->setText($text);
        $post->setUser($user);
        $post->setUsername($user->getName());
        $post->setdelUserId($userId);
        $post->setDate(time());

        $this->knPostRepository->save($post);

        if ($plot !== null) {
            $this->newKnPostNotificator->notify($post, $plot);
        }

        $game->addInformation(_('Der Beitrag wurde hinzugefügt'));

        if ($mark !== 0) {
            $user->setKNMark($post->getId());

            $this->userRepository->save($user);
        }

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
