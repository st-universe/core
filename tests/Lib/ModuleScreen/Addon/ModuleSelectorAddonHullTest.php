<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen\Addon;

use Mockery\MockInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\StuTestCase;

class ModuleSelectorAddonHullTest extends StuTestCase
{
    /** @var MockInterface&TorpedoHullRepositoryInterface */
    private MockInterface $torpedoHullRepository;

    /** @var MockInterface&GradientColorInterface */
    private MockInterface $gradientColor;

    private ModuleSelectorAddonHull $subject;

    protected function setUp(): void
    {
        $this->torpedoHullRepository = $this->mock(TorpedoHullRepositoryInterface::class);
        $this->gradientColor = $this->mock(GradientColorInterface::class);

        $this->subject = new ModuleSelectorAddonHull(
            $this->torpedoHullRepository,
            $this->gradientColor
        );
    }

    public function testCalculateGradientColor(): void
    {
        $this->torpedoHullRepository->shouldReceive('getModificatorMinAndMax')
            ->withNoArgs()
            ->once()
            ->andReturn([222, 444]);

        $this->gradientColor->shouldReceive('calculateGradientColor')
            ->with(42, 222, 444)
            ->andReturn('colorA');

        $this->subject->calculateGradientColor(42);
        $result = $this->subject->calculateGradientColor(42);

        $this->assertEquals('colorA', $result);
    }
}
