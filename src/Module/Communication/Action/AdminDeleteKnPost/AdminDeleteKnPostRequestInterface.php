<?php

namespace Stu\Module\Communication\Action\AdminDeleteKnPost;

interface AdminDeleteKnPostRequestInterface
{
    public function getKnId(): int;
    public function getReason(): string;
}
