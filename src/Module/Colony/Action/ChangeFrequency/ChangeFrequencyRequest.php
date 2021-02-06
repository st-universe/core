<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeFrequency;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeFrequencyRequest implements ChangeFrequencyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getFrequency(): int
    {
        return $this->queryParameter('frequency')->int()->required();
    }
}
