<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\TopicSettings;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class TopicSettingsRequest implements TopicSettingsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}
