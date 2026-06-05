<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

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

        static::assertArrayHasKey('buildings_id', $fieldWithStorage);
        static::assertArrayNotHasKey('id', $fieldWithStorage);
        static::assertSame(12345, $fieldWithStorage['buildings_id']);
        static::assertSame(82010100, $fieldWithHeadquarter['buildings_id']);
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

        static::fail(sprintf('field with field_id %d not found', $fieldId));
    }
}
