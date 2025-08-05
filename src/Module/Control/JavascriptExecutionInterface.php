<?php

namespace Stu\Module\Control;

use Stu\Component\Game\JavascriptExecutionTypeEnum;

interface JavascriptExecutionInterface
{
    /** @return array<string>|null */
    public function getExecuteJS(JavascriptExecutionTypeEnum $when): ?array;

    public function addExecuteJS(
        string $value,
        JavascriptExecutionTypeEnum $when
    ): void;

    public static function reset(): void;
}
