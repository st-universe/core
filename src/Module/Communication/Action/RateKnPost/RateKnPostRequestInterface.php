<?php

namespace Stu\Module\Communication\Action\RateKnPost;

interface RateKnPostRequestInterface {

    public function getPostId(): int;

    public function getRating(): int;
}
