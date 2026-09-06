<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action\ModerateCrewRace;

use request;
use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\View\ShowCrewRaceModeration\ShowCrewRaceModeration;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ModerateCrewRace implements ActionControllerInterface, AccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MODERATE_CREW_RACE';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly UserCrewRaceRepositoryInterface $userCrewRaceRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::CREW_RACE_MODERATION;
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceModeration::VIEW_IDENTIFIER);

        $crewRace = $this->crewRaceRepository->find(request::postInt('crew_race_id'));
        $decision = request::postString('decision');
        if (
            $crewRace === null
            || !$crewRace->isCustom()
            || $crewRace->isAccepted()
            || $crewRace->getAcceptedUserId() !== null
            || !in_array($decision, ['accept', 'reject'], true)
        ) {
            $game->getInfo()->addInformation(_('Die Crew-Rasse kann nicht moderiert werden'));
            return;
        }

        $accepted = $decision === 'accept';
        $reason = trim((string)request::postString('rejection_reason'));
        if (mb_strlen($reason) > 2000) {
            $game->getInfo()->addInformation(_('Der Ablehnungsgrund darf höchstens 2000 Zeichen enthalten'));
            return;
        }

        $crewRace
            ->setRejectionReason($accepted || $reason === '' ? null : $reason)
            ->setAccepted($accepted)
            ->setAcceptedUserId($game->getUser()->getId());
        $this->crewRaceRepository->save($crewRace);

        $creatorUserId = $crewRace->getCreatorUserId();
        if ($accepted && $creatorUserId !== null && !$this->userCrewRaceRepository->exists($crewRace->getId(), $creatorUserId)) {
            $this->userCrewRaceRepository->save(
                $this->userCrewRaceRepository->prototype()
                    ->setCrewRace($crewRace)
                    ->setUserId($creatorUserId)
            );
        }

        if ($creatorUserId !== null && $this->userRepository->find($creatorUserId) !== null) {
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $creatorUserId,
                $accepted
                    ? sprintf(_('Deine Crew-Rasse %s wurde akzeptiert'), $crewRace->getDescription())
                    : sprintf(_('Deine Crew-Rasse %s wurde abgelehnt'), $crewRace->getDescription())
                        . ($reason === '' ? '' : "\n\nBegründung: " . $reason)
                        . "\n\nDu kannst sie unter Optionen > Crew-Rassen überarbeiten und erneut einreichen",
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                'options.php?SHOW_CREW_RACE_MANAGEMENT=1'
            );
        }

        $game->getInfo()->addInformation($accepted ? _('Die Crew-Rasse wurde akzeptiert') : _('Die Crew-Rasse wurde abgelehnt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
