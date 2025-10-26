<?php

namespace Stu\Module\Control;

use Stu\Component\Game\JavascriptExecutionTypeEnum;

final class JavascriptExecution implements JavascriptExecutionInterface
{
    /** @var array<int, array<string>> */
    private static array $execjs = [];

    #[\Override]
    public function getExecuteJS(JavascriptExecutionTypeEnum $when): ?array
    {
        if (!array_key_exists($when->value, self::$execjs)) {
            return null;
        }

        return self::$execjs[$when->value];
    }

    #[\Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when): void
    {
        match ($when) {
            JavascriptExecutionTypeEnum::BEFORE_RENDER =>
            self::$execjs[$when->value][] = $value,
            JavascriptExecutionTypeEnum::AFTER_RENDER =>
            self::$execjs[$when->value][] = $value,
            JavascriptExecutionTypeEnum::ON_AJAX_UPDATE =>
            self::$execjs[$when->value][] = $value
        };
    }

    #[\Override]
    public static function reset(): void
    {
        self::$execjs = [];
    }
}
