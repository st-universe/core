<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

use RPGPlotData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPlotList\ShowPlotList;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;

final class CreateKnPlot implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_PLOT';

    private $createKnPlotRequest;

    private $rpgPlotMemberRepository;

    public function __construct(
        CreateKnPlotRequestInterface $createKnPlotRequest,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository
    ) {
        $this->createKnPlotRequest = $createKnPlotRequest;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $title = $this->createKnPlotRequest->getTitle();
        $description = $this->createKnPlotRequest->getText();

        $plot = new RPGPlotData();

        $plot->setTitle($title);
        $plot->setDescription($description);
        if (mb_strlen($title) < 6) {
            $game->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }

        $plot->setUserId($userId);
        $plot->setStartDate(time());
        $plot->save();

        $member = $this->rpgPlotMemberRepository->prototype();
        $member->setUserId($userId);
        $member->setPlotId((int) $plot->getId());

        $this->rpgPlotMemberRepository->save($member);

        $game->addInformation(_('Der Plot wurde erstellt'));

        $game->setView(ShowPlotList::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
