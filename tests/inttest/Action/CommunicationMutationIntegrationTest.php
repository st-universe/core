<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\CommunicationMutationIntegrationTestCase;
use Stu\Orm\Entity\RpgPlot;

final class CommunicationMutationIntegrationTest extends CommunicationMutationIntegrationTestCase
{
    public static function actions(): iterable
    {
        yield 'CreateKnPlot' => ['Stu\\Module\\Communication\\Action\\CreateKnPlot\\CreateKnPlot'];
        yield 'PostKnComment' => ['Stu\\Module\\Communication\\Action\\PostKnComment\\PostKnComment'];
        yield 'DeleteKnComment' => ['Stu\\Module\\Communication\\Action\\DeleteKnComment\\DeleteKnComment'];
        yield 'AddKnPlotMember' => ['Stu\\Module\\Communication\\Action\\AddKnPlotMember\\AddKnPlotMember'];
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

    public function testInactivePlotCannotGainMembers(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $em->find(RpgPlot::class, $this->ids['plot'])->setEndDate(time());
        $em->flush();
        $before = $this->state();
        $this->dispatch('Stu\\Module\\Communication\\Action\\AddKnPlotMember\\AddKnPlotMember', 'POST', ['plotid' => $this->ids['plot'], 'memid' => 103, 'sstr' => $this->token(101)]);
        self::assertSame($before, $this->state());
    }

}
