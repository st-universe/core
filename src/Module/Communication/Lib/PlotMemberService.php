<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;

final class PlotMemberService implements PlotMemberServiceInterface
{
    public function __construct(
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    public function addUserToPlotIfExists(NPCQuest $quest, User $user): void
    {
        $plot = $quest->getPlot();

        if ($plot === null || !$plot->isActive()) {
            return;
        }

        if ($this->rpgPlotMemberRepository->getByPlotAndUser($plot->getId(), $user->getId()) !== null) {
            return;
        }

        $member = $this->rpgPlotMemberRepository->prototype()
            ->setUser($user)
            ->setRpgPlot($plot);

        $this->rpgPlotMemberRepository->save($member);

        $this->privateMessageSender->send(
            $quest->getUserId(),
            $user->getId(),
            sprintf(
                'Du wurdest dem RPG-Plot \'%s\' als Schreiber hinzugefügt (Quest: %s)',
                $plot->getTitle(),
                $quest->getTitle()
            )
        );
    }
}