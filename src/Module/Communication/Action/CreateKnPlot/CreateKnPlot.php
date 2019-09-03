<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPlotList\ShowPlotList;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class CreateKnPlot implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_PLOT';

    private $createKnPlotRequest;

    private $rpgPlotMemberRepository;

    private $rpgPlotRepository;

    public function __construct(
        CreateKnPlotRequestInterface $createKnPlotRequest,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->createKnPlotRequest = $createKnPlotRequest;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $title = $this->createKnPlotRequest->getTitle();
        $description = $this->createKnPlotRequest->getText();

        if (mb_strlen($title) < 6) {
            $game->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }

        $plot = $this->rpgPlotRepository->prototype()
            ->setTitle($title)
            ->setDescription($description)
            ->setUserId($userId)
            ->setStartDate(time());

        $this->rpgPlotRepository->save($plot);

        $member = $this->rpgPlotMemberRepository->prototype()
            ->setUserId($userId)
            ->setRpgPlot($plot);

        $this->rpgPlotMemberRepository->save($member);

        $plot->getMembers()->add($member);

        $game->addInformation(_('Der Plot wurde erstellt'));

        $game->setView(ShowPlotList::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
