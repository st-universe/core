<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestWith;
use request;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\StuTestCase;

class SpawnPirateFleetTest extends StuTestCase
{
    private PirateCreationInterface&MockInterface $pirateCreation;
    private MapRepositoryInterface&MockInterface $mapRepository;
    private ActionControllerContext&MockInterface $context;
    private Layer&MockInterface $layer;
    private SpawnPirateFleet $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->pirateCreation = $this->mock(PirateCreationInterface::class);
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->context = $this->mock(ActionControllerContext::class);
        $this->layer = $this->mock(Layer::class);
        $layerRepository->shouldReceive('find')->with(3)->once()->andReturn($this->layer);
        $this->layer->shouldReceive('getName')->andReturn('Walker');
        $this->context->shouldReceive('setView')->with(ShowScripts::VIEW_IDENTIFIER)->once();

        $this->subject = new SpawnPirateFleet($this->pirateCreation, $this->mapRepository, $layerRepository);
    }

    #[TestWith(['', '', null])]
    #[TestWith(['  ', '  ', 4])]
    #[TestWith([null, null, 4])]
    public function testRandomSpawnUsesSelectedLayerAndReportsActualCoordinates(?string $cx, ?string $cy, ?int $setupId): void
    {
        request::setMockVars(array_filter(
            ['pirate_layer' => 3, 'pirate_cx' => $cx, 'pirate_cy' => $cy, 'pirate_setup_id' => $setupId ?? 0],
            static fn (mixed $value): bool => $value !== null
        ));
        $field = $this->mock(Map::class);
        $this->mapRepository->shouldReceive('getRandomPirateSpawnLocation')->with($this->layer)->once()->andReturn($field);
        $this->expectFleetCreation($field, $setupId);

        $this->subject->handle($this->context);
    }

    public function testLayerWithoutAllowedLocationDoesNotCreateFleet(): void
    {
        request::setMockVars(['pirate_layer' => 3, 'pirate_cx' => '', 'pirate_cy' => '']);
        $this->mapRepository->shouldReceive('getRandomPirateSpawnLocation')->with($this->layer)->once()->andReturnNull();
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Auf Layer Walker können keine Piraten gespawnt werden, da keine zulässige Spawnposition vorhanden ist')->once();

        $this->subject->handle($this->context);
    }

    #[TestWith(['12', ''])]
    #[TestWith(['', '34'])]
    public function testIncompleteCoordinatesDoNotCreateFleet(string $cx, string $cy): void
    {
        request::setMockVars(['pirate_layer' => 3, 'pirate_cx' => $cx, 'pirate_cy' => $cy]);
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Bitte beide Koordinaten angeben oder beide für eine zufällige Spawnposition leer lassen')->once();

        $this->subject->handle($this->context);
    }

    public function testExplicitCoordinatesAreUsedDirectly(): void
    {
        request::setMockVars(['pirate_layer' => 3, 'pirate_cx' => '12', 'pirate_cy' => '34', 'pirate_setup_id' => 4]);
        $field = $this->mock(Map::class);
        $field->shouldReceive('getFieldType')->andReturn(new MapFieldType()->setPassable(true));
        $this->mapRepository->shouldReceive('getByCoordinates')->with($this->layer, 12, 34)->once()->andReturn($field);
        $this->expectFleetCreation($field, 4);

        $this->subject->handle($this->context);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function testInvalidExplicitLocationDoesNotCreateFleet(bool $exists): void
    {
        request::setMockVars(['pirate_layer' => 3, 'pirate_cx' => '12', 'pirate_cy' => '34']);
        $field = $exists ? $this->mock(Map::class) : null;
        $field?->shouldReceive('getFieldType')->andReturn(new MapFieldType());
        $this->mapRepository->shouldReceive('getByCoordinates')->with($this->layer, 12, 34)->once()->andReturn($field);
        $this->context->shouldReceive('getInfo->addInformation')
            ->with($exists ? 'Die Position Walker|12|34 ist nicht passierbar!' : 'Die Position Walker|12|34 existiert nicht!')->once();

        $this->subject->handle($this->context);
    }

    private function expectFleetCreation(Map&MockInterface $field, ?int $setupId): void
    {
        $field->shouldReceive('getX')->andReturn(12);
        $field->shouldReceive('getY')->andReturn(34);
        $fleet = new Fleet()->setName('Kazon');
        $fleet->getShips()->add(new Ship());
        $this->pirateCreation->shouldReceive('createPirateFleet')->with(null, $setupId, $field)->once()->andReturn($fleet);
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Piratenflotte "Kazon" mit 1 Schiffen wurde bei Walker|12|34 gespawnt')->once();
    }
}
