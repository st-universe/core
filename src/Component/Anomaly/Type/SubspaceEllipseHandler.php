<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Override;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollection;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

//TODO unit tests
final class SubspaceEllipseHandler implements AnomalyHandlerInterface
{
    public const int MASS_CALCULATION_THRESHOLD = 33_333_333;

    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private AnomalyCreationInterface $anomalyCreation,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ApplyDamageInterface $applyDamage,
        private PrivateMessageSenderInterface $privateMessageSender,
        private DistributedMessageSenderInterface $distributedMessageSender,
        private StuRandom $stuRandom,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function checkForCreation(): void
    {
        $subspaceEllipses = [];

        foreach ($this->locationRepository->getForSubspaceEllipseCreation() as $location) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SUBSPACE_ELLIPSE,
                $location
            );
        }

        foreach ($subspaceEllipses as $anomaly) {
            $this->informSpacecraftOwnersAboutCreation($anomaly);
        }
    }

    #[Override]
    public function handleSpacecraftTick(AnomalyInterface $anomaly): void
    {
        $location = $anomaly->getLocation();
        $spacecrafts = $location->getSpacecrafts();

        $messagesForShips = new MessageCollection();
        $messagesForBases = new MessageCollection();

        $intro = $this->messageFactory->createMessage(
            UserEnum::USER_NOONE,
            null
        );

        $messagesForShips->add($intro);
        $messagesForBases->add($intro);

        foreach ($spacecrafts as $spacecraft) {

            if ($spacecraft->getUser()->isVacationRequestOldEnough()) {
                continue;
            }

            if (!$spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SYSTEM_SHIELDS)) {
                continue;
            }

            $shieldSystem = $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_SHIELDS);

            if (
                $spacecraft->getShield() === 0
                && $shieldSystem->getStatus() === 0
            ) {
                continue;
            }

            $message = $this->messageFactory->createMessage(UserEnum::USER_NOONE, $spacecraft->getUser()->getId());
            $message->add($spacecraft->getName());

            if ($shieldSystem->getMode()->isActivated()) {
                $shieldSystem->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }

            if ($spacecraft->getShield() > 0) {
                $spacecraft->setShield(0);
                $message->add('- die Schilde wurden entladen');
            }

            if ($shieldSystem->getStatus() > 0) {
                $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

                $informations = new InformationWrapper();
                $this->applyDamage->damageShipSystem(
                    $wrapper,
                    $shieldSystem,
                    $this->stuRandom->rand(1, 50, true),
                    $informations
                );
                $message->addMessageMerge($informations->getInformations());
            }

            if ($spacecraft->isStation()) {
                $messagesForBases->add($message);
            } else {
                $messagesForShips->add($message);
            }

            $this->spacecraftRepository->save($spacecraft);
        }

        $this->informSpacecraftOwnersAboutConsequences(
            $anomaly->getLocation()->getSectorString(),
            $messagesForShips,
            $messagesForBases
        );
    }

    #[Override]
    public function letAnomalyDisappear(AnomalyInterface $anomaly): void
    {
        //TODO
    }

    private function informSpacecraftOwnersAboutCreation(AnomalyInterface $anomaly): void
    {
        $usersToInform = [];

        $location = $anomaly->getLocation();
        $spacecrafts = $location->getSpacecrafts();

        foreach ($spacecrafts as $spacecraft) {
            $usersToInform[$spacecraft->getUser()->getId()] = $spacecraft->getUser();
        }

        foreach ($usersToInform as $user) {
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $user->getId(),
                sprintf('[b][color=yellow]In Sektor %s ist aufgrund des immensen Energiepotentials eine Subraumellipse entstanden![/color][/b]', $location->getSectorString())
            );
        }
    }

    private function informSpacecraftOwnersAboutConsequences(
        string $sectorString,
        MessageCollectionInterface $messageCollectionForShips,
        MessageCollectionInterface $messageCollectionForBases
    ): void {

        $header = sprintf(
            "[b][color=red]Subraumellipse in Sektor %s[/color][/b]",
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollectionForShips,
            UserEnum::USER_NOONE,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $header
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollectionForBases,
            UserEnum::USER_NOONE,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $header
        );
    }
}
