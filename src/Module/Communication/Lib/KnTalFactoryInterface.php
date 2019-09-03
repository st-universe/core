<?php

namespace Stu\Module\Communication\Lib;

use KNPostingData;
use UserData;

interface KnTalFactoryInterface
{
    public function createKnPostTal(KNPostingData $post, UserData $user): KnPostTalInterface;
}