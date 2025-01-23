<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\Definition\ArrayDefinition;
use DI\Definition\AutowireDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Source\MutableDefinitionSource;
use Mockery\MockInterface;
use Override;
use Psr\Container\ContainerInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\StuTestCase;

class StuContainerTest extends StuTestCase
{
    /** @var MockInterface&MutableDefinitionSource */
    private $definitionSource;
    /** @var MockInterface&ContainerInterface */
    private $wrapperContainer;

    private StuContainer $subject;

    #[Override]
    public function setUp(): void
    {
        $this->definitionSource = $this->mock(MutableDefinitionSource::class);
        $this->wrapperContainer = $this->mock(ContainerInterface::class);

        $this->subject = new StuContainer(
            $this->definitionSource,
            null,
            $this->wrapperContainer
        );
    }

    public function testGetDefinedImplementationsOfExpectAllEntriesOfArrayDefinition(): void
    {
        $definition = $this->mock(ArrayDefinition::class);
        $implementation1 = $this->mock(RepairUtilInterface::class);
        $implementation2 = $this->mock(RepairUtilInterface::class);

        $this->definitionSource->shouldReceive('getDefinitions')
            ->withNoArgs()
            ->once()
            ->andReturn(['DEFINITION_NAME' => $definition]);

        $this->wrapperContainer->shouldReceive('get')
            ->with('DEFINITION_NAME')
            ->once()
            ->andReturn([
                'KEY1' => $implementation1,
                'KEY2' => $implementation2
            ]);

        $this->subject->getDefinedImplementationsOf(RepairUtilInterface::class);
        $result = $this->subject->getDefinedImplementationsOf(RepairUtilInterface::class);

        $this->assertFalse($result->isEmpty());
        $this->assertEquals(2, $result->count());
        $this->assertEquals([
            'KEY1' => $implementation1,
            'KEY2' => $implementation2
        ], $result->toArray());
    }

    public function testGetDefinedImplementationsOfExpectSeveralAutowireDefinitions(): void
    {
        $definition1 = $this->mock(AutowireDefinition::class);
        $definition2 = $this->mock(AutowireDefinition::class);
        $definition3 = $this->mock(AutowireDefinition::class);
        $definitionWithUnwantedType = $this->mock(FactoryDefinition::class);
        $implementation1 = $this->mock(RepairUtilInterface::class);
        $implementation2 = $this->mock(RepairUtilInterface::class);
        $implementation3 = $this->mock(SpacecraftSystemManagerInterface::class);

        $this->definitionSource->shouldReceive('getDefinitions')
            ->withNoArgs()
            ->once()
            ->andReturn([
                'DEFINITION_NAME_1' => $definition1,
                'DEFINITION_NAME_2' => $definition2,
                'DEFINITION_NAME_3' => $definition3,
                'DEFINITION_NAME_4' => $definitionWithUnwantedType
            ]);

        $this->wrapperContainer->shouldReceive('get')
            ->with('DEFINITION_NAME_1')
            ->once()
            ->andReturn($implementation1);
        $this->wrapperContainer->shouldReceive('get')
            ->with('DEFINITION_NAME_2')
            ->once()
            ->andReturn($implementation2);
        $this->wrapperContainer->shouldReceive('get')
            ->with('DEFINITION_NAME_3')
            ->once()
            ->andReturn($implementation3);

        $this->subject->getDefinedImplementationsOf(RepairUtilInterface::class);
        $result = $this->subject->getDefinedImplementationsOf(RepairUtilInterface::class);

        $this->assertFalse($result->isEmpty());
        $this->assertEquals(2, $result->count());
        $this->assertEquals([
            'DEFINITION_NAME_1' => $implementation1,
            'DEFINITION_NAME_2' => $implementation2
        ], $result->toArray());
    }
}
