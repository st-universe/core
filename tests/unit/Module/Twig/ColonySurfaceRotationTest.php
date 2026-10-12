<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use ReflectionProperty;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ColonySurfaceRotationTest extends StuTestCase
{
    public function testStoredScansUseAllReadableRotations(): void
    {
        $twig = $this->createTwig();

        foreach ([0, 90, 180, 270, '0', '90', '180', '270'] as $rotation) {
            $html = $twig->render('html/database/colonySurface.twig', [
                'SURFACE' => [[
                    'field_id' => 1,
                    'type_id' => 101,
                    'buildings_id' => 12345,
                    'rotation' => $rotation
                ]],
                'CSSCLASS' => 'cfu',
                'SURFACETILESTYLE' => ''
            ]);

            self::assertStringContainsString(sprintf('buildings/12345/%sat.png', $rotation), $html);
        }
    }

    public function testStoredScansDefaultMissingAndUnreadableRotationsToZero(): void
    {
        $surface = [[
            'field_id' => 1,
            'type_id' => 101,
            'buildings_id' => 12345,
            'rotation' => 270
        ], [
            'field_id' => 2,
            'type_id' => 101,
            'id' => 12345
        ]];

        foreach ([null, false, true, 45, 360, -90, 'invalid', '../90', [], new \stdClass()] as $rotation) {
            $surface[] = [
                'field_id' => count($surface) + 1,
                'type_id' => 101,
                'buildings_id' => 12345,
                'rotation' => $rotation
            ];
        }

        $html = $this->createTwig()->render('html/database/colonySurface.twig', [
            'SURFACE' => $surface,
            'CSSCLASS' => 'cfu',
            'SURFACETILESTYLE' => ''
        ]);

        self::assertSame(1, substr_count($html, 'buildings/12345/270at.png'));
        self::assertSame(count($surface) - 1, substr_count($html, 'buildings/12345/0at.png'));
    }

    public function testLiveSurfacesUseFieldRotationDuringDayAndNight(): void
    {
        foreach (['t', 'n'] as $dayNightPrefix) {
            $twig = $this->createTwig($dayNightPrefix);

            foreach ([0, 90, 180, 270] as $rotation) {
                $field = $this->createField($rotation);

                foreach ([
                    'html/colony/component/colonySurface.twig',
                    'html/ship/colonyscan.twig',
                    'html/alliance/alliancecolonysurface.twig'
                ] as $template) {
                    $html = $twig->render($template, [
                        'SURFACE' => [
                            'getSurface' => [$field],
                            'getSurfaceTileStyle' => ''
                        ]
                    ]);

                    self::assertStringContainsString(sprintf('buildings/12345/%da%s.png', $rotation, $dayNightPrefix), $html);
                }
            }
        }
    }

    public function testConstructionUsesRotationForBothBuildingImages(): void
    {
        $field = $this->createField(270);
        $field->setActive(time() + 100);

        $html = $this->createTwig('n')->render('html/colony/component/colonySurface.twig', [
            'SURFACE' => [
                'getSurface' => [$field],
                'getSurfaceTileStyle' => ''
            ]
        ]);

        self::assertStringContainsString('buildings/12345/270bn.png', $html);
        self::assertStringContainsString('buildings/12345/270an.png', $html);
    }

    public function testFieldPopupOffersRotationOnlyForCompletedRotatableBuildings(): void
    {
        foreach ([true, false] as $rotatable) {
            foreach ([1, time() + 100] as $active) {
                $field = $this->createField(180);
                $building = $field->getBuilding();
                self::assertNotNull($building);
                $building->setRotation($rotatable);
                $field->setActive($active);

                $html = $this->createTwig('n')->render('html/colony/component/fieldAction.twig', [
                    'FIELD' => $field,
                    'HOST' => $field->getHost()
                ]);

                self::assertSame($rotatable && $active === 1 ? 2 : 0, substr_count($html, 'class="building-rotation"'));
                self::assertStringContainsString('buildings/12345/180an.png', $html);

                if ($rotatable && $active === 1) {
                    self::assertStringContainsString("rotateBuilding(1, 'right')", $html);
                    self::assertStringContainsString("rotateBuilding(1, 'left')", $html);
                }
            }
        }
    }

    private function createField(int $rotation): PlanetField
    {
        $building = new Building();
        new ReflectionProperty(Building::class, 'id')->setValue($building, 12345);
        $building->setName('Rotierbares Gebäude');
        $building->setIntegrity(100);
        $building->setBuildtime(100);

        $colony = new Colony();
        new ReflectionProperty(Colony::class, 'id')->setValue($colony, 42);

        $field = new PlanetField();
        new ReflectionProperty(PlanetField::class, 'id')->setValue($field, 1);
        $field->setColony($colony);
        $field->setBuilding($building);
        $field->setIntegrity(100);
        $field->setActive(1);
        $field->setRotation($rotation);

        return $field;
    }

    private function createTwig(string $dayNightPrefix = 't'): Environment
    {
        $twig = new Environment(new FilesystemLoader('src'));
        $twig->addFunction(new TwigFunction('getDayNightPrefix', fn (): string => $dayNightPrefix));

        foreach (['planetFieldTitle', 'planetFieldTypeDescription', 'htmlSafe', 'formatSeconds', 'stuDateTime', 'addPlusCharacter', 'formatProductionValue'] as $filter) {
            $twig->addFilter(new TwigFilter($filter, fn (): string => ''));
        }

        return $twig;
    }
}
