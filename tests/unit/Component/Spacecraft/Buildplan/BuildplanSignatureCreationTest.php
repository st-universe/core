<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Buildplan;

use Stu\Orm\Entity\Module;
use Stu\StuTestCase;

class BuildplanSignatureCreationTest extends StuTestCase
{
    private BuildplanSignatureCreationInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->subject = new BuildplanSignatureCreation();
    }

    public function testCeateBuildplanSignature(): void
    {
        $moduleA = $this->mock(Module::class);
        $moduleB = $this->mock(Module::class);
        $moduleC = $this->mock(Module::class);
        $moduleD = $this->mock(Module::class);

        $moduleA->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(4);
        $moduleB->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2);
        $moduleC->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
        $moduleD->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(3);

        $modules = [$moduleA, $moduleB, $moduleC, $moduleD];

        $signature = $this->subject->createSignature($modules, 42);

        $this->assertEquals(md5('1_2_3_4_42'), $signature);
    }
}
