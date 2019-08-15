<?php

namespace Stu\Control;

use Stu\Lib\SessionInterface;

final class IntermediateController extends GameController
{

    public const TYPE_DATABASE = 'DATABASE';

    public function __construct(
        SessionInterface $session,
        array $actions,
        array $views
    )
    {
        parent::__construct($session,'', '');

        foreach ($views as $key => $view) {
            $this->addView($key, $view);
        }
    }
}
