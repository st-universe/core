<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemPoolInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Repository\PlanetFieldTypeRepositoryInterface;

final class PlanetFieldTypeRetriever implements PlanetFieldTypeRetrieverInterface
{
    private const CACHE_KEY = 'planet_field_type_list';

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
        if (!$this->cache->hasItem(static::CACHE_KEY)) {
            $cacheData = [];

            foreach ($this->planetFieldTypeRepository->findAll() as $field) {
                $cacheData[$field->getFieldType()] = $field->getDescription();
            }

            $cacheItem = new CacheItem(static::CACHE_KEY);
            $cacheItem->set($cacheData);
            $cacheItem->expiresAfter(static::CACHE_TTL);

            $this->cache->save($cacheItem);
        }

        return $this->cache->getItem(static::CACHE_KEY)->get()[$fieldTypeId] ?? '';
    }
}
