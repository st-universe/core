<?php

namespace Stu\Module\Communication\Action\AddKnPost;

interface AddKnPostRequestInterface
{
    public function getPostMark(): int;

    public function getPlotId(): int;

    public function getText(): string;

    public function getTitle(): string;

    public function getCharacterIds(): string;
}
