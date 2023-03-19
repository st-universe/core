<?php

declare(strict_types=1);

namespace Stu\Module\History\View\Overview;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OverviewRequest implements OverviewRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTypeId(array $possibleTypeIds, int $default): int
    {
        $value = $this->queryParameter('htype')->int()->defaultsTo($default);
        if (!in_array($value, $possibleTypeIds)) {
            return $default;
        }
        return $value;
    }

    public function getCount(int $default): int
    {
        return $this->queryParameter('hcount')->int()->defaultsTo($default);
    }
}
