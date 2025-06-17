<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\StarsystemGenerator\Component\AsteroidRingGenerator;
use Stu\StarsystemGenerator\Component\AsteroidRingGeneratorInterface;
use Stu\StarsystemGenerator\Component\LoadSystemConfiguration;
use Stu\StarsystemGenerator\Component\LoadSystemConfigurationInterface;
use Stu\StarsystemGenerator\Component\MassCenterGenerator;
use Stu\StarsystemGenerator\Component\MassCenterGeneratorInterface;
use Stu\StarsystemGenerator\Component\MoonPlacement;
use Stu\StarsystemGenerator\Component\MoonPlacementInterface;
use Stu\StarsystemGenerator\Component\PlanetMoonGenerator;
use Stu\StarsystemGenerator\Component\PlanetMoonGeneratorInterface;
use Stu\StarsystemGenerator\Component\PlanetPlacement;
use Stu\StarsystemGenerator\Component\PlanetPlacementInterface;
use Stu\StarsystemGenerator\Component\PlanetRingPlacement;
use Stu\StarsystemGenerator\Component\PlanetRingPlacementInterface;
use Stu\StarsystemGenerator\Component\SizeGenerator;
use Stu\StarsystemGenerator\Component\SizeGeneratorInterface;
use Stu\StarsystemGenerator\Config\PlanetMoonProbabilities;
use Stu\StarsystemGenerator\Config\PlanetMoonProbabilitiesInterface;
use Stu\StarsystemGenerator\StarsystemGenerator;
use Stu\StarsystemGenerator\StarsystemGeneratorInterface;

use function DI\autowire;

return [
    LoadSystemConfigurationInterface::class => autowire(LoadSystemConfiguration::class),
    SizeGeneratorInterface::class => autowire(SizeGenerator::class),
    MassCenterGeneratorInterface::class => autowire(MassCenterGenerator::class),
    AsteroidRingGeneratorInterface::class => autowire(AsteroidRingGenerator::class),
    PlanetMoonProbabilitiesInterface::class => autowire(PlanetMoonProbabilities::class),
    PlanetPlacementInterface::class => autowire(PlanetPlacement::class),
    PlanetRingPlacementInterface::class => autowire(PlanetRingPlacement::class),
    MoonPlacementInterface::class => autowire(MoonPlacement::class),
    PlanetMoonGeneratorInterface::class => autowire(PlanetMoonGenerator::class),
    StarsystemGeneratorInterface::class => autowire(StarsystemGenerator::class),
    StarSystemCreationInterface::class => autowire(StarSystemCreation::class),
    GenerateEmptySystemsInterface::class => autowire(GenerateEmptySystems::class),
];
