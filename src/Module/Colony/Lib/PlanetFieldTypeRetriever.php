<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemPoolInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Repository\PlanetFieldTypeRepositoryInterface;

final class PlanetFieldTypeRetriever implements PlanetFieldTypeRetrieverInterface
{
    private const CACHE_KEY_NAME = 'planet_field_type_list';
    private const CACHE_KEY_CATEGORY = 'planet_field_type_categories';

    private const CACHE_TTL = TimeConstants::ONE_DAY_IN_SECONDS;

    private CacheItemPoolInterface $cache;

    private PlanetFieldTypeRepositoryInterface $planetFieldTypeRepository;

    public function __construct(
        CacheItemPoolInterface $cache,
        PlanetFieldTypeRepositoryInterface $planetFieldTypeRepository
    ) {
        $this->cache = $cache;
        $this->planetFieldTypeRepository = $planetFieldTypeRepository;
    }

    public function getDescription(int $fieldTypeId): string
    {
        if (!$this->cache->hasItem(static::CACHE_KEY_NAME)) {
            $this->fillCache(static::CACHE_KEY_NAME, 'getDescription');
        }

        return  $this->cache->getItem(static::CACHE_KEY_NAME)->get()[$fieldTypeId] ?? '';
    }

    public function getCategory(int $fieldTypeId): int
    {
        if (!$this->cache->hasItem(static::CACHE_KEY_CATEGORY)) {
            $this->fillCache(static::CACHE_KEY_CATEGORY, 'getCategory');
        }

        return  $this->cache->getItem(static::CACHE_KEY_CATEGORY)->get()[$fieldTypeId];
    }

    private function fillCache(string $cacheKey, string $method): void
    {
        $cacheData = [];

        foreach ($this->planetFieldTypeRepository->findAll() as $field) {
            $cacheData[$field->getFieldType()] = $field->$method();
        }

        $cacheItem = new CacheItem($cacheKey);
        $cacheItem->set($cacheData);
        $cacheItem->expiresAfter(static::CACHE_TTL);

        $this->cache->save($cacheItem);
    }
}
