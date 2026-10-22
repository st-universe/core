<?php

declare(strict_types=1);

namespace Stu\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\CommunicationMutationIntegrationTestCase;

final class PlotEditIntegrationTest extends CommunicationMutationIntegrationTestCase
{
    public static function actions(): iterable
    {
        yield 'EditKnPlot' => ['Stu\\Module\\Communication\\Action\\EditKnPlot\\EditKnPlot'];
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

}
