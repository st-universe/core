<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Orm\Entity\ColonyInterface;
use Stu\StuTestCase;

class ColonyFunctionManagerTest extends StuTestCase
{
    private ColonyFunctionManager $subject;

    protected function setUp(): void
    {
        $this->subject = new ColonyFunctionManager();
    }

    public function testHasActiveFunctionReturnsValue(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $functionId = 666;

        $colony->shouldReceive('hasActiveBuildingWithFunction')
            ->with($functionId)
            ->once()
            ->andReturnTrue();

        static::assertTrue(
            $this->subject->hasActiveFunction($colony, $functionId)
        );
    }
}
