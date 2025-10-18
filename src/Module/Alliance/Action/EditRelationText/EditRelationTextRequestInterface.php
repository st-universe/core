<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditRelationText;

interface EditRelationTextRequestInterface
{
    public function getRelationId(): int;

    public function getText(): string;
}