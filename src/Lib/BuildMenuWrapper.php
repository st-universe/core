<?php

declare(strict_types=1);

class BuildMenuWrapper
{

    function __get($id)
    {
        return new BuildMenu($id);
    }

}