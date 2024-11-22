<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteTopicRequest implements DeleteTopicRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTopicId(): int
    {
        return $this->parameter('topicid')->int()->required();
    }
}
