<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\TimeConstants;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EmptyPlotDeletion implements MaintenanceHandlerInterface
{
    public const int MAX_AGE_IN_SECONDS = TimeConstants::SEVEN_DAYS_IN_SECONDS;

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[\Override]
    public function handle(): void
    {
        $txtTemplate = _('Der Plot "%s" wurde gelöscht, da er veraltet ist und keine Beiträge enthält.');

        foreach ($this->rpgPlotRepository->getEmptyOldPlots(self::MAX_AGE_IN_SECONDS) as $plot) {
            // send deletion messages
            foreach ($plot->getMembers() as $member) {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $member->getUser()->getId(),
                    sprintf($txtTemplate, $plot->getTitle())
                );

                $this->rpgPlotMemberRepository->delete($member);
            }

            // delete plot
            $this->rpgPlotRepository->delete($plot);
        }
    }
}
