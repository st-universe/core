<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteTopicRequest implements DeleteTopicRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}
