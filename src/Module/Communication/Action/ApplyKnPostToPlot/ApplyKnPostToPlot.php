<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ApplyKnPostToPlot;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\KnPostToPlotApplicationRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ApplyKnPostToPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_APPLY_POST_TO_PLOT';

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository, private KnPostRepositoryInterface $knPostRepository, private KnPostToPlotApplicationRepositoryInterface $knPostToPlotApplicationRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $plot = $this->rpgPlotRepository->find(request::postIntFatal('plotid'));
        if ($plot === null || $plot->getUserId() !== $userId || !$plot->isActive()) {
            return;
        }

        $postId = request::postInt('addknid');

        if ($postId === 0) {
            return;
        }

        $post = $this->knPostRepository->find($postId);
        if ($post === null) {
            $game->addInformation(_('Dieser Beitrag existiert nicht'));
            return;
        }
        if ($post->getPlotId() !== null) {
            $game->addInformation(_('Dieser Beitrag ist bereits einem Plot zugewiesen'));
            return;
        }

        $application = $this->knPostToPlotApplicationRepository->getByPostAndPlot($post->getId(), $plot->getId());

        if ($application !== null) {
            $game->addInformation(_('Diese Aktion wurde bereits beantragt'));
            return;
        }

        $isOwnPost = $post->getUserId() === $userId;

        if ($isOwnPost) {
            $post->setRpgPlot($plot);
            $this->knPostRepository->save($post);

            $this->notifyPlotMembers($post, $plot);

            $game->addInformation(_('Der Beitrag wurde hinzugefügt'));
        } else {
            $application = $this->knPostToPlotApplicationRepository->prototype();
            $application->setKnPost($post);
            $application->setRpgPlot($plot);
            $application->setTime(time());
            $this->knPostToPlotApplicationRepository->save($application);

            $href = sprintf(_('comm.php?B_ADD_POST_TO_PLOT=1&knid=%d&plotid=%d'), $post->getId(), $plot->getId());

            $this->privateMessageSender->send(
                $userId,
                $post->getUser()->getId(),
                sprintf(
                    _('Der Spieler %s hat beantragt deinen Beitrag mit der ID %d und Titel "%s" zu dem RPG-Plot "%s" hinzuzufügen. Zum Annehmen den Link klicken, sonst ignorieren. Erlischt nach 48 Stunden.'),
                    $game->getUser()->getName(),
                    $post->getId(),
                    $post->getTitle(),
                    $plot->getTitle()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $href
            );

            $game->addInformation(_('Es wurde beantragt den Beitrag hinzuzufügen'));
        }
    }

    private function notifyPlotMembers(KnPost $post, RpgPlot $plot): void
    {
        foreach ($plot->getMembers() as $member) {
            if ($member->getUser() !== $post->getUser()) {
                $user = $member->getUser();

                $text = sprintf(
                    _('Der Beitrag mit ID %d und Titel "%s" wurde nachträglich zum Plot "%s" hinzugefügt.'),
                    $post->getId(),
                    $post->getTitle(),
                    $plot->getTitle()
                );

                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $user->getId(),
                    $text,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                    $post
                );
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
