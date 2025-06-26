<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen\Addon;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\TorpedoHull;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\StuTestCase;

class ModuleSelectorAddonHullTest extends StuTestCase
{
    private MockInterface&TorpedoHullRepositoryInterface $torpedoHullRepository;

    private MockInterface&GradientColorInterface $gradientColor;

    private ModuleSelectorAddonHull $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->torpedoHullRepository = $this->mock(TorpedoHullRepositoryInterface::class);
        $this->gradientColor = $this->mock(GradientColorInterface::class);

        $this->subject = new ModuleSelectorAddonHull(
            $this->torpedoHullRepository,
            $this->gradientColor
        );
    }

    public function testGetModificators(): void
    {
        $torpedoModule = $this->mock(Module::class);
        $torpedoHull1 = $this->mock(TorpedoHull::class);
        $torpedoHull2 = $this->mock(TorpedoHull::class);
        $torpedoType1 = $this->mock(TorpedoType::class);
        $torpedoType2 = $this->mock(TorpedoType::class);

        $torpedoModule->shouldReceive('getTorpedoHull')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$torpedoHull1, $torpedoHull2]));

        $torpedoHull1->shouldReceive('getModificator')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $torpedoHull1->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoType1);

        $torpedoHull2->shouldReceive('getModificator')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $torpedoHull2->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoType2);

        $this->torpedoHullRepository->shouldReceive('getModificatorMinAndMax')
            ->withNoArgs()
            ->once()
            ->andReturn([222, 444]);

        $this->gradientColor->shouldReceive('calculateGradientColor')
            ->with(1, 222, 444)
            ->andReturn('colorA');
        $this->gradientColor->shouldReceive('calculateGradientColor')
            ->with(2, 222, 444)
            ->andReturn('colorB');

        $result = $this->subject->getModificators($torpedoModule);

        $this->assertEquals($torpedoType1, $result[0]['torpedoType']);
        $this->assertEquals('colorA', $result[0]['gradientColor']);
        $this->assertEquals(1, $result[0]['modificator']);

        $this->assertEquals($torpedoType2, $result[1]['torpedoType']);
        $this->assertEquals('colorB', $result[1]['gradientColor']);
        $this->assertEquals(2, $result[1]['modificator']);
    }
}
