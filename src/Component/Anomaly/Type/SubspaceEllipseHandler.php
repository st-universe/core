<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Override;
use RuntimeException;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
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
        private SystemDamageInterface $systemDamage,
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

            if ($location->isAnomalyForbidden()) {
                continue;
            }

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
    public function handleSpacecraftTick(Anomaly $anomaly): void
    {
        $location = $anomaly->getLocation();
        if ($location === null) {
            throw new RuntimeException('this should not happen');
        }

        $spacecrafts = $location->getSpacecraftsWithoutVacation();

        $messagesForShips = $this->messageFactory->createMessageCollection();
        $messagesForBases = $this->messageFactory->createMessageCollection();

        $intro = $this->messageFactory->createMessage(
            UserEnum::USER_NOONE,
            null
        );

        $messagesForShips->add($intro);
        $messagesForBases->add($intro);

        foreach ($spacecrafts as $spacecraft) {

            if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
                continue;
            }

            $shieldSystem = $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS);
            $condition = $spacecraft->getCondition();

            if (
                $condition->getShield() === 0
                && !$shieldSystem->isHealthy()
            ) {
                continue;
            }

            $message = $this->messageFactory->createMessage(UserEnum::USER_NOONE, $spacecraft->getUser()->getId());
            $message->add($spacecraft->getName());

            if ($shieldSystem->getMode()->isActivated()) {
                $shieldSystem->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }

            if ($condition->getShield() > 0) {
                $condition->setShield(0);
                $message->add('- die Schilde wurden entladen');
            }

            if ($shieldSystem->getStatus() > 0) {
                $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

                $informations = new InformationWrapper();
                $this->systemDamage->damageShipSystem(
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
            $location->getSectorString(),
            $messagesForShips,
            $messagesForBases
        );
    }

    #[Override]
    public function letAnomalyDisappear(Anomaly $anomaly): void
    {
        //TODO
    }

    private function informSpacecraftOwnersAboutCreation(Anomaly $anomaly): void
    {
        $usersToInform = [];

        $location = $anomaly->getLocation();
        if ($location === null) {
            throw new RuntimeException('this should not happen');
        }

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

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, Anomaly $anomaly, MessageCollectionInterface $messages): void
    {
        //not needed
    }
}
