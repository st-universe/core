<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RateKnPost;

interface RateKnPostRequestInterface
{
    public function getKnId(): int;

    public function getRating(): int;
}
