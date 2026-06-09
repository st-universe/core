<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

final class RealtimeChannels
{
    public const string STARMAP_SPACECRAFT_STREAM = 'stu:realtime:starmap:spacecraft';
    public const string STARMAP_COVERAGE_KEY_PREFIX = 'stu:realtime:starmap:coverage:';

    public static function starmapCoverageKey(int $userId, int $layerId): string
    {
        return sprintf('%s%d:%d', self::STARMAP_COVERAGE_KEY_PREFIX, $userId, $layerId);
    }
}
