<?php

namespace Stu\Module\Control;

use Override;
use Stu\Component\Game\JavascriptExecutionTypeEnum;

final class JavascriptExecution implements JavascriptExecutionInterface
{
    /** @var array<int, array<string>> */
    private array $execjs = [];

    #[Override]
    public function getExecuteJS(JavascriptExecutionTypeEnum $when): ?array
    {
        if (!array_key_exists($when->value, $this->execjs)) {
            return null;
        }

        return $this->execjs[$when->value];
    }

    #[Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when): void
    {
        match ($when) {
            JavascriptExecutionTypeEnum::BEFORE_RENDER =>
            $this->execjs[$when->value][] = $value,
            JavascriptExecutionTypeEnum::AFTER_RENDER =>
            $this->execjs[$when->value][] = $value,
            JavascriptExecutionTypeEnum::ON_AJAX_UPDATE =>
            $this->execjs[$when->value][] = $value
        };
    }
}
