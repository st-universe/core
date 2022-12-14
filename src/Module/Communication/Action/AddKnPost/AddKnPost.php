<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
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

    private PrivateMessageSenderInterface $privateMessageSender;

    private EntityManagerInterface $entityManager;

    public function __construct(
        AddKnPostRequestInterface $addKnPostRequest,
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        UserRepositoryInterface $userRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        EntityManagerInterface $entityManager
    ) {
        $this->addKnPostRequest = $addKnPostRequest;
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->userRepository = $userRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

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
            /** @var RpgPlotInterface $plot */
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
        $post->setDate(time());

        $this->knPostRepository->save($post);
        $this->entityManager->flush();

        if ($plot !== null) {
            $this->notifyPlotMembers($post, $plot);
        }

        $game->addInformation(_('Der Beitrag wurde hinzugefügt'));

        if ($mark) {
            $user->setKNMark($post->getId());

            $this->userRepository->save($user);
        }

        $game->setView(GameController::DEFAULT_VIEW);
    }

    private function notifyPlotMembers(KnPostInterface $post, RpgPlotInterface $plot): void
    {
        foreach ($plot->getMembers() as $member) {
            if ($member->getUser() !== $post->getUser()) {
                $user = $member->getUser();

                $text = sprintf(
                    _('Der Spieler %s hat einen neuen Beitrag zum Plot "%s" hinzugefügt.'),
                    $post->getUser()->getName(),
                    $plot->getTitle()
                );

                $href = sprintf(_('comm.php?SHOW_SINGLE_KN=1&id=%d'), $post->getId());

                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $user->getId(),
                    $text,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
                    $href
                );
            }
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
