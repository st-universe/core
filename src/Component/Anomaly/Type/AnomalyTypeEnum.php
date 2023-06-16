<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

final class AnomalyTypeEnum
{
    // anomaly types
    public const ANOMALY_TYPE_SUBSPACE_ELLIPSE = 1;

    public static function getDescription(int $anomalyType): string
    {
        switch ($anomalyType) {
            case static::ANOMALY_TYPE_SUBSPACE_ELLIPSE:
                return _("Subraumellipse");
        }
        return '';
    }
}
