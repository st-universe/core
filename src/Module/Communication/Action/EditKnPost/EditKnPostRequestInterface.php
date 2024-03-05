<?php

namespace Stu\Module\Communication\Action\EditKnPost;

interface EditKnPostRequestInterface
{
    public function getPostId(): int;

    public function getPlotId(): int;

    public function getText(): string;

    public function getTitle(): string;

    public function getCharacterIds(): string;
}
