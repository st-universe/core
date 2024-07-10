<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

use Override;
use Stu\Module\Communication\View\ShowPlotList\ShowPlotList;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class CreateKnPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_PLOT';

    public function __construct(private CreateKnPlotRequestInterface $createKnPlotRequest, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $title = $this->createKnPlotRequest->getTitle();
        $description = $this->createKnPlotRequest->getText();
        $user = $game->getUser();

        if (mb_strlen($title) < 6) {
            $game->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }

        if (mb_strlen($title) > 80) {
            $game->addInformation(_('Der Titel ist zu lang (maximal 80 Zeichen)'));
            return;
        }

        $plot = $this->rpgPlotRepository->prototype()
            ->setTitle($title)
            ->setDescription($description)
            ->setUser($user)
            ->setStartDate(time());

        $this->rpgPlotRepository->save($plot);

        $member = $this->rpgPlotMemberRepository->prototype()
            ->setUser($user)
            ->setRpgPlot($plot);

        $this->rpgPlotMemberRepository->save($member);

        $plot->getMembers()->add($member);

        $game->addInformation(_('Der Plot wurde erstellt'));

        $game->setView(ShowPlotList::VIEW_IDENTIFIER);
        $game->addInformation(_('Der Plot wurde erstellt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
