<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use JsonMapper\JsonMapperInterface;
use Override;
use RuntimeException;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

//TODO unit tests
final class IonStormHandler implements AnomalyHandlerInterface
{
    private const int LOCATIONS_PER_STORM = 3000;
    private const int DRIVE_DEACTIVATION_MEAN_SECONDS = TimeConstants::THIRTY_MINUTES_IN_SECONDS;

    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private LocationRepositoryInterface $locationRepository,
        private LayerRepositoryInterface $layerRepository,
        private AnomalyCreationInterface $anomalyCreation,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private LocationPoolFactory $locationPoolFactory,
        private IonStormPropagation $ionStormPropagation,
        private IonStormMovement $ionStormMovement,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private ApplyDamageInterface $applyDamage,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private PrivateMessageSenderInterface $privateMessageSender,
        private MessageFactoryInterface $messageFactory,
        private JsonMapperInterface $jsonMapper,
        private InformationFactoryInterface $informationFactory,
        private StuRandom $stuRandom,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function checkForCreation(): void
    {
        $count = $this->anomalyRepository->getActiveCountByTypeWithoutParent(AnomalyTypeEnum::ION_STORM);
        $missing = $this->getMaxIonStormCount() - $count;

        while ($missing > 0) {

            $randomLocation = $this->locationRepository->getRandomLocation();
            if (
                !$randomLocation->hasAnomaly(AnomalyTypeEnum::ION_STORM)
                && !$randomLocation->isAnomalyForbidden()
            ) {
                $missing--;
                $root = $this->anomalyCreation->create(
                    AnomalyTypeEnum::ION_STORM,
                    null,
                    null,
                    IonStormData::createRandomInstance($this->stuRandom)
                );
                $this->anomalyCreation->create(
                    AnomalyTypeEnum::ION_STORM,
                    $randomLocation,
                    $root
                );
            }
        }
    }

    private function getMaxIonStormCount(): int
    {
        return array_reduce(
            $this->layerRepository->findAllIndexed(),
            fn(int $value, LayerInterface $layer): int => $value + (int)ceil($layer->getWidth() * $layer->getHeight() / self::LOCATIONS_PER_STORM),
            0
        );
    }

    #[Override]
    public function handleSpacecraftTick(AnomalyInterface $root): void
    {
        $ionStormData = $this->getIonStormData($root);
        $locationPool = $this->locationPoolFactory->createLocationPool($root, $ionStormData->velocity + 1);

        $this->ionStormMovement->moveStorm(
            $root,
            $ionStormData,
            $locationPool
        );

        $this->ionStormPropagation->propagateStorm($root, $locationPool);

        $this->damageSpacecrafts($root);
    }

    private function damageSpacecrafts(AnomalyInterface $root): void
    {
        foreach ($root->getChildren() as $child) {
            $location = $child->getLocation();
            if ($location === null) {
                throw new RuntimeException('this should not happen');
            }

            foreach ($location->getSpacecraftsWithoutVacation() as $spacecraft) {

                $userId = $spacecraft->getUser()->getId();
                $informations = $this->informationFactory->createInformationWrapper();
                $informations->addInformationf("Die %s in Sektor %s befindet sich in einem gefährlichen Ionensturm.\n", $spacecraft->getName(), $location->getSectorString());

                $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

                if ($spacecraft instanceof ShipInterface) {
                    $this->damageSpacecraft(
                        $wrapper,
                        $child,
                        $informations,
                        10
                    );
                } else {
                    $this->deactivateShields($wrapper, $informations);
                }

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $userId,
                    $informations,
                    $spacecraft->getType()->getMessageFolderType(),
                    $spacecraft->isDestroyed() ? null : $spacecraft->getHref()
                );
            }
        }
    }

    private function deactivateShields(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();

        $system = $spacecraft->getSystems()[SpacecraftSystemTypeEnum::SHIELDS->value] ?? null;
        if (
            $system !== null
            && $system->getMode()->isActivated()
        ) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, true);
            $informations->addInformation('Die Schilde sind ausgefallen');
        }

        $this->spacecraftRepository->save($spacecraft);
    }

    private function getIonStormData(AnomalyInterface $root): IonStormData
    {
        $data = $root->getData();
        if ($data === null) {
            throw new RuntimeException('this should not happen');
        }

        return $this->jsonMapper->mapObjectFromString(
            $data,
            new IonStormData()
        );
    }

    #[Override]
    public function letAnomalyDisappear(AnomalyInterface $anomaly): void
    {
        //not needed
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, AnomalyInterface $anomaly, MessageCollectionInterface $messages): void
    {
        $message = $this->messageFactory->createMessage(
            UserEnum::USER_NOONE,
            $wrapper->get()->getUser()->getId(),
            [sprintf("In Sektor %s befindet sich ein gefährlicher Ionensturm.\n", $wrapper->get()->getSectorString())]
        );
        $messages->add($message);

        $this->deactivateDrive($wrapper, $message);
        $this->damageSpacecraft($wrapper, $anomaly, $message, 20);
    }

    private function deactivateDrive(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();

        $systemType = $spacecraft->isWarpPossible()
            ? SpacecraftSystemTypeEnum::WARPDRIVE
            : SpacecraftSystemTypeEnum::IMPULSEDRIVE;

        $system = $spacecraft->getSystems()[$systemType->value] ?? null;
        if ($system !== null) {
            $this->spacecraftSystemManager->deactivate($wrapper, $systemType, true);
            $system->setCooldown($this->stuTime->time() + $this->stuRandom->rand(0, 2 * self::DRIVE_DEACTIVATION_MEAN_SECONDS, true, TimeConstants::TEN_MINUTES_IN_SECONDS, 2));

            $informations->addInformationf('Der %s ist ausgefallen und kann vorerst nicht mehr aktiviert werden', $systemType->getDescription());
        }

        $this->spacecraftRepository->save($spacecraft);
    }

    private function damageSpacecraft(SpacecraftWrapperInterface $wrapper, AnomalyInterface $anomaly, InformationInterface $informations, int $damagePercentage): void
    {
        $spacecraft = $wrapper->get();

        $damageWrapper = new DamageWrapper(
            (int)ceil($this->stuRandom->rand(1, $damagePercentage, true) * $wrapper->get()->getMaxHull() / 100)
        );

        $shield = $spacecraft->getSystems()[SpacecraftSystemTypeEnum::SHIELDS->value] ?? null;
        $shieldLevel = $shield !== null ? $shield->determineSystemLevel() : 0;

        $damageWrapper
            ->setCrit(random_int(0, 20) === 0)
            ->setHullDamageFactor($this->getHullDamageFactor($spacecraft))
            ->setShieldDamageFactor(100 - $shieldLevel * 10)
            ->setTargetSystemTypes([
                SpacecraftSystemTypeEnum::DEFLECTOR,
                SpacecraftSystemTypeEnum::LSS,
                SpacecraftSystemTypeEnum::NBS,
                SpacecraftSystemTypeEnum::IMPULSEDRIVE,
                SpacecraftSystemTypeEnum::WARPDRIVE,
            ]);

        $this->applyDamage->damage($damageWrapper, $wrapper, $informations);

        if ($spacecraft->isDestroyed()) {

            $this->spacecraftDestruction->destroy(
                $anomaly,
                $wrapper,
                SpacecraftDestructionCauseEnum::ANOMALY_DAMAGE,
                $informations
            );
        }
    }

    private function getHullDamageFactor(SpacecraftInterface $spacecraft): int
    {
        $hull = $spacecraft->getModules()[SpacecraftModuleTypeEnum::HULL->value] ?? null;
        if ($hull === null) {
            return 100;
        }

        $hasIonStormReduction = $hull->hasSpecial(ModuleSpecialAbilityEnum::ION_STORM_DAMAGE_REDUCTION);

        return max(0, 100 - $hull->getLevel() * ($hasIonStormReduction ? 16 : 8));
    }
}
