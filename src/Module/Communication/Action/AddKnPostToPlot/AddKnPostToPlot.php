<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPostToPlot;

use Override;
use request;
use Stu\Component\Game\TimeConstants;
use Stu\Exception\SanityCheckException;
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

final class AddKnPostToPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_POST_TO_PLOT';

    public const int MAXIMUM_APPLICATION_TIME = TimeConstants::TWO_DAYS_IN_SECONDS;

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository, private KnPostRepositoryInterface $knPostRepository, private KnPostToPlotApplicationRepositoryInterface $knPostToPlotApplicationRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $postId = request::getIntFatal('knid');
        $plotId = request::getIntFatal('plotid');

        $application = $this->knPostToPlotApplicationRepository->getByPostAndPlot($postId, $plotId);

        if ($application === null || $application->getTime() < time() - self::MAXIMUM_APPLICATION_TIME) {
            $game->addInformation('Diese Aktion ist nicht mehr möglich');
            return;
        }

        if ($application->getKnPost()->getUser()->getId() !== $userId) {
            return;
        }

        $plot = $this->rpgPlotRepository->find($plotId);
        if ($plot === null || !$plot->isActive()) {
            return;
        }

        $post = $this->knPostRepository->find($postId);
        if ($post === null) {
            throw new SanityCheckException(sprintf('postId %d does not exist', $postId));
        }

        if ($post->getPlotId() !== null) {
            $this->knPostToPlotApplicationRepository->delete($application);
            $game->addInformation('Dieser Beitrag ist bereits einem Plot zugewiesen');
            return;
        }

        $post->setRpgPlot($plot);
        $this->knPostRepository->save($post);
        $this->knPostToPlotApplicationRepository->delete($application);

        $this->notifyPlotMembers($post, $plot);

        $game->addInformation('Der Beitrag wurde hinzugefügt');
    }

    private function notifyPlotMembers(KnPost $post, RpgPlot $plot): void
    {
        foreach ($plot->getMembers() as $member) {
            if ($member->getUser() !== $post->getUser()) {
                $user = $member->getUser();

                $text = sprintf(
                    'Der Beitrag mit ID %d und Titel "%s" wurde nachträglich zum Plot "%s" hinzugefügt.',
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
