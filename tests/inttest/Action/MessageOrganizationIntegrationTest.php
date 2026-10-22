<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\CommunicationMutationIntegrationTestCase;
use Stu\Exception\AccessViolationException;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;

final class MessageOrganizationIntegrationTest extends CommunicationMutationIntegrationTestCase
{
    public static function actions(): iterable
    {
        yield 'MovePm' => ['Stu\\Module\\Message\\Action\\MovePm\\MovePm'];
        yield 'EditPmCategory' => ['Stu\\Module\\Message\\Action\\EditPmCategory\\EditPmCategory'];
        yield 'SortPmCategories' => ['Stu\\Module\\Message\\Action\\SortPmCategories\\SortPmCategories'];
        yield 'DeleteBuildPlan' => ['Stu\\Module\\Colony\\Action\\DeleteBuildPlan\\DeleteBuildPlan'];
        yield 'AddBoard' => ['Stu\\Module\\Alliance\\Action\\AddBoard\\AddBoard'];
    }

    #[DataProvider('actions')]
    public function testForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks(string $class): void
    {
        $this->assertForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks($class);
    }

    public function testOwnTokenCannotChangeForeignObjects(): void
    {
        $this->assertOwnTokenCannotChangeForeignObjects();
    }

    public function testUsedBuildplanCannotBeDeleted(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $plan = $em->find(SpacecraftBuildplan::class, $this->ids['plan']);
        $ship = $em->find(Spacecraft::class, 42);
        $ship->setBuildplan($plan);
        $plan->getSpacecraftList()->add($ship);
        $em->flush();
        self::assertGreaterThan(0, $plan->getSpacecraftCount());
        $before = $this->state();
        $this->dispatch('Stu\\Module\\Colony\\Action\\DeleteBuildPlan\\DeleteBuildPlan', 'POST', ['planid' => $this->ids['plan'], 'sstr' => $this->token(101)]);
        self::assertSame($before, $this->state());
    }

    public function testMovingOwnMessageToForeignFolderIsRejected(): void
    {
        $before = $this->state();
        $this->dispatch('Stu\\Module\\Message\\Action\\MovePm\\MovePm', 'POST', ['move_pm' => $this->ids['message'], 'movecat_' . $this->ids['message'] => $this->ids['foreignFolder'], 'sstr' => $this->token(101)]);
        self::assertSame($before, $this->state());
    }

    public function testBoardCreationRequiresActualAlliancePermission(): void
    {
        $this->getContainer()->get(AllianceMemberJobRepositoryInterface::class)->truncateByUser(101);
        $before = $this->state();
        try {
            $this->dispatch('Stu\\Module\\Alliance\\Action\\AddBoard\\AddBoard', 'POST', ['board' => 'Unerlaubtes Forum', 'sstr' => $this->token(101)]);
            self::fail('Unberechtigtes Erstellen eines Forums wurde erlaubt');
        } catch (AccessViolationException) {
            self::assertSame($before, $this->state());
        }
    }

}
