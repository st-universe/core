<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RateKnPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class RateKnPostRequest implements RateKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->bodyParameter('postid')->int()->required();
    }

    public function getRating(): int
    {
        return (int)$this->bodyParameter('rating')->oneOf([1, -1])->required();
    }
}
