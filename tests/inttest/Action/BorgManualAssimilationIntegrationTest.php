<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationCapture;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Action\BorgAssimilateTarget\BorgAssimilateTarget;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\RumpAssimilateable;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;

final class BorgManualAssimilationIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $cube;
    private Ship $first;
    private Ship $selected;
    private BorgAssimilateTarget $action;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->cube = $this->entity(Ship::class, 77);
        $this->first = $this->entity(Ship::class, 42);
        $this->selected = $this->entity(Ship::class, 78);
        $colony = $this->entity(Colony::class, 42);
        foreach ([$this->cube, $this->first, $this->selected] as $ship) {
            $ship->getFleet()?->getShips()->getValues();
            $ship->setFleet(null)->setIsFleetLeader(false)->setDockedTo(null)->setLocation($colony->getStarsystemMap());
            $ship->getCondition()->setDisabled(false)->setIsDestroyed(false);
            $ship->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER);
            $this->system($ship, SpacecraftSystemTypeEnum::WARPDRIVE)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $this->system($ship, SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)) {
                $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $this->cube->setUser($this->entity(User::class, 19))->setName('Manueller Testkubus');
        $this->selected->setName('Gezielt ausgewähltes Schiff');
        $this->system($this->cube, SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $fleet = $this->entity(Fleet::class, 42);
        $fleet->setUser($this->first->getUser())->setLeadShip($this->first);
        $this->first->setFleet($fleet)->setIsFleetLeader(true);
        $this->selected->setFleet($fleet)->setIsFleetLeader(false);
        $location = $this->cube->getLocation();
        $location->getSpacecrafts()->clear();
        foreach ([$this->cube, $this->first, $this->selected] as $ship) {
            $location->getSpacecrafts()->set($ship->getId(), $ship);
        }
        $quest = new NPCQuest()->setUser($this->cube->getUser())->setUserId(19)->setTitle('Manuelle Assimilation')->setStart($this->now - 100);
        $configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($this->cube->getUser())
            ->setBuildplan($this->entity(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 86400)
            ->setSettings(['assimilation_enabled' => 1, 'assimilation_chance_percent' => 0, 'assimilation_duration_seconds' => 900, 'nanite_commodity_id' => 2]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($this->cube->getId())->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->em->persist($quest);
        $this->em->persist($configuration);
        $this->em->persist($event);
        self::assertSame(6501, $this->selected->getRump()->getId());
        $this->em->persist(new RumpAssimilateable()->setRump($this->selected->getRump()));
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand', 'array_rand');
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $capture = $container->make(BorgAssimilationCapture::class, ['random' => $random, 'relations' => $relations]);
        $this->action = $container->make(BorgAssimilateTarget::class, ['assimilation' => $capture]);
        request::setMockVars([BorgAssimilateTarget::ACTION_IDENTIFIER => 1, 'id' => $this->cube->getId(), 'target' => $this->selected->getId()]);
    }

    public function testTheRealActionCapturesTheSecondSelectedShipAndPersistsItsFleetTractorEntryAndWarning(): void
    {
        self::assertNotNull(self::$testSession);
        self::$testSession->setUserById(19);
        $container = $this->getContainer();
        $context = $container->get(ActionContextFactoryInterface::class)->createActionContext($container->get(GameControllerInterface::class));
        $messagesBefore = $this->em->getRepository(PrivateMessage::class)->count(['send_user' => 19, 'recip_user' => 101]);

        $this->em->wrapInTransaction(fn () => $this->action->handle($context));

        $this->em->clear();
        $cube = $this->entity(Ship::class, 77);
        $first = $this->entity(Ship::class, 42);
        $selected = $this->entity(Ship::class, 78);
        $entry = $this->em->find(SpacecraftAssimilated::class, 78);
        self::assertNotNull($entry);
        self::assertTrue($entry->isActive());
        self::assertSame(0, $entry->getProgress());
        self::assertSame($this->now, $entry->getStartedAt());
        self::assertSame(900, $entry->getDurationSeconds());
        self::assertSame($cube, $entry->getSource());
        self::assertNull($this->em->find(SpacecraftAssimilated::class, 42));
        self::assertFalse($first->isBeingAssimilated());
        self::assertSame(101, $selected->getUser()->getId());
        self::assertNull($selected->getFleet());
        self::assertSame($this->entity(Fleet::class, 42), $first->getFleet());
        self::assertTrue($first->isFleetLeader());
        self::assertSame($selected, $cube->getTractoredShip());
        self::assertSame($cube, $selected->getTractoringSpacecraft());
        self::assertTrue($cube->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
        self::assertTrue($cube->isShielded());
        self::assertTrue($selected->isShielded());
        self::assertContains('Die Assimilation der Gezielt ausgewähltes Schiff wurde begonnen', $context->getInfo()->getInformations());
        $messages = $this->em->getRepository(PrivateMessage::class)->findBy(['send_user' => 19, 'recip_user' => 101], ['id' => 'ASC']);
        self::assertCount($messagesBefore + 1, $messages);
        $message = $messages[array_key_last($messages)];
        self::assertStringContainsString('Gezielt ausgewähltes Schiff', $message->getText());
        self::assertStringContainsString('Borg-Drohnen beginnen mit der Assimilation', $message->getText());
        self::assertStringContainsString('innerhalb von 15 Minuten', $message->getText());
    }

    public function testAForgedRequestCannotControlAnotherOwnersCubeOrMutateItsTargets(): void
    {
        self::assertNotNull(self::$testSession);
        self::$testSession->setUserById(102);
        $container = $this->getContainer();
        $context = $container->get(ActionContextFactoryInterface::class)->createActionContext($container->get(GameControllerInterface::class));
        $messagesBefore = $this->em->getRepository(PrivateMessage::class)->count([]);
        $rejected = false;

        $this->em->wrapInTransaction(function () use ($context, &$rejected): void {
            try {
                $this->action->handle($context);
            } catch (AccessViolationException) {
                $rejected = true;
            }
        });

        self::assertTrue($rejected);
        self::assertSame(0, $this->em->getRepository(SpacecraftAssimilated::class)->count([]));
        self::assertNull($this->cube->getTractoredShip());
        self::assertFalse($this->cube->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
        self::assertFalse($this->first->isBeingAssimilated());
        self::assertFalse($this->selected->isBeingAssimilated());
        self::assertSame(101, $this->selected->getUser()->getId());
        self::assertSame($this->first->getFleet(), $this->selected->getFleet());
        self::assertSame($messagesBefore, $this->em->getRepository(PrivateMessage::class)->count([]));
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function entity(string $class, int $id): object
    {
        $entity = $this->em->find($class, $id);
        self::assertNotNull($entity);
        return $entity;
    }

    private function system(Ship $ship, SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        if (!$ship->hasSpacecraftSystem($type)) {
            $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type);
            $ship->getSystems()->set($type->value, $system);
            $this->em->persist($system);
        }
        return $ship->getSpacecraftSystem($type)->setStatus(100);
    }
}
