<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EmptyPlotDeletion implements MaintenanceHandlerInterface
{
    public const MAX_AGE_IN_SECONDS = TimeConstants::SEVEN_DAYS_IN_SECONDS;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        RpgPlotRepositoryInterface $rpgPlotRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(): void
    {
        $txtTemplate = _('Der Plot "%s" wurde gelöscht, da er veraltet ist und keine Beiträge enthält.');

        foreach ($this->rpgPlotRepository->getEmptyOldPlots(self::MAX_AGE_IN_SECONDS) as $plot) {
            // send deletion messages
            foreach ($plot->getMembers() as $member) {
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $member->getUser()->getId(),
                    sprintf($txtTemplate, $plot->getTitle())
                );
            }

            // delete plot
            $this->rpgPlotRepository->delete($plot);
        }
    }
}
