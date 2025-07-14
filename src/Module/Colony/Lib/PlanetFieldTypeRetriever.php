<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Cache\Adapter\Common\CacheItem;
use Override;
use Psr\Cache\CacheItemPoolInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldTypeRepositoryInterface;

/**
 * Provides service methods to determine field types and -descriptions
 */
final class PlanetFieldTypeRetriever implements PlanetFieldTypeRetrieverInterface
{
    public const int FIELD_TYPE_CATEGORY_SURFACE = 1;
    private const int FIELD_TYPE_CATEGORY_ORBIT = 2;
    private const int FIELD_TYPE_CATEGORY_UNDERGROUND = 3;

    private const string CACHE_KEY_NAME = 'planet_field_type_list';
    private const string CACHE_KEY_CATEGORY = 'planet_field_type_categories';

    private const int CACHE_TTL = TimeConstants::ONE_DAY_IN_SECONDS;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private CacheItemPoolInterface $cache,
        private PlanetFieldTypeRepositoryInterface $planetFieldTypeRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function getDescription(int $fieldTypeId): string
    {
        if (!$this->cache->hasItem(self::CACHE_KEY_NAME)) {
            $this->fillCache(self::CACHE_KEY_NAME, 'getDescription');
        }

        return $this->cache->getItem(self::CACHE_KEY_NAME)->get()[$fieldTypeId] ?? '';
    }

    #[Override]
    public function getCategory(int $fieldTypeId): int
    {
        if (!$this->cache->hasItem(self::CACHE_KEY_CATEGORY)) {
            $this->fillCache(self::CACHE_KEY_CATEGORY, 'getCategory');
        }

        if ($fieldTypeId === 1000) {
            return 0;
        }

        $result = $this->cache->getItem(self::CACHE_KEY_CATEGORY)->get()[$fieldTypeId];

        if ($result === null) {
            $this->loggerUtil->init('CACHE', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf('could not retrieve category for fieldTypeId: %s', $fieldTypeId));
        }

        return $result ?? 0;
    }

    #[Override]
    public function isUndergroundField(
        PlanetField $planetField
    ): bool {
        return $this->isTypeOf(
            $planetField,
            self::FIELD_TYPE_CATEGORY_UNDERGROUND
        );
    }

    #[Override]
    public function isOrbitField(
        PlanetField $planetField
    ): bool {
        return $this->isTypeOf(
            $planetField,
            self::FIELD_TYPE_CATEGORY_ORBIT
        );
    }

    private function isTypeOf(
        PlanetField $planetField,
        int $fieldCategory
    ): bool {
        return $this->getCategory($planetField->getFieldType()) === $fieldCategory;
    }

    private function fillCache(string $cacheKey, string $method): void
    {
        $cacheData = [];

        foreach ($this->planetFieldTypeRepository->findAll() as $field) {
            $cacheData[$field->getFieldType()] = $field->$method();
        }

        $cacheItem = new CacheItem($cacheKey);
        $cacheItem->set($cacheData);
        $cacheItem->expiresAfter(self::CACHE_TTL);

        $this->cache->save($cacheItem);
    }
}
