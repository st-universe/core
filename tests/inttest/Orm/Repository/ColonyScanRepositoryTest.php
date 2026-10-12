<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;

class ColonyScanRepositoryTest extends IntegrationTestCase
{
    public function testGetSurfaceReturnsBuildingIdWithExpectedKey(): void
    {
        $surface = $this->getContainer()
            ->get(ColonyScanRepositoryInterface::class)
            ->getSurface(42);

        $fieldWithStorage = $this->getFieldByFieldId($surface, 23);
        $fieldWithHeadquarter = $this->getFieldByFieldId($surface, 24);

        self::assertArrayHasKey('buildings_id', $fieldWithStorage);
        self::assertArrayNotHasKey('id', $fieldWithStorage);
        self::assertSame(12345, $fieldWithStorage['buildings_id']);
        self::assertSame(82010100, $fieldWithHeadquarter['buildings_id']);
        self::assertSame(0, $fieldWithStorage['rotation']);
        self::assertSame(0, $fieldWithHeadquarter['rotation']);
    }

    public function testGetSurfaceIncludesStoredRotation(): void
    {
        $container = $this->getContainer();
        $field = $container->get(PlanetFieldRepositoryInterface::class)->getByColonyAndFieldIndex(42, 23);
        self::assertNotNull($field);

        $field->setRotation(270);
        $entityManager = $container->get(EntityManagerInterface::class);
        $entityManager->flush();

        try {
            $surface = $container->get(ColonyScanRepositoryInterface::class)->getSurface(42);

            self::assertSame(270, $this->getFieldByFieldId($surface, 23)['rotation']);
        } finally {
            $field->setRotation(0);
            $entityManager->flush();
        }
    }

    /**
     * @param array<array<string, int|null>> $surface
     *
     * @return array<string, int|null>
     */
    private function getFieldByFieldId(array $surface, int $fieldId): array
    {
        foreach ($surface as $field) {
            if ($field['field_id'] === $fieldId) {
                return $field;
            }
        }

        self::fail(sprintf('field with field_id %d not found', $fieldId));
    }
}
