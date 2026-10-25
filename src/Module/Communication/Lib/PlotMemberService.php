<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;

final class PlotMemberService implements PlotMemberServiceInterface
{
    public function __construct(
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function addUserToPlotIfExists(NPCQuest $quest, User $user): void
    {
        $plot = $quest->getPlot();

        if ($plot === null || !$plot->isActive()) {
            return;
        }

        if ($this->rpgPlotMemberRepository->getByPlotAndUser($plot->getId(), $user->getId()) !== null) {
            return;
        }

        $member = $this->rpgPlotMemberRepository
            ->prototype()
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

    #[\Override]
    public function notifyMembers(RpgPlot $plot, string $message): void
    {
        foreach ($plot->getMembers() as $member) {
            $recipientId = $member->getUser()->getId();
            if ($recipientId === $plot->getUserId()) {
                continue;
            }

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $recipientId,
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                sprintf('/communication.php?SHOW_PLOT=1&plotid=%d', $plot->getId())
            );
        }
    }
}
