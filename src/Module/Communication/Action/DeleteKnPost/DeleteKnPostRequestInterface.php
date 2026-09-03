<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

interface DeleteKnPostRequestInterface
{
    public function getKnId(): int;
}
