<?php

namespace Stu\Control;

use Stu\Lib\SessionInterface;

final class IntermediateController extends GameController
{

    public const TYPE_DATABASE = 'DATABASE';
    public const TYPE_RESEARCH = 'RESEARCH';
    public const TYPE_MAINDESK = 'MAINDESK';
    public const TYPE_NOTES = 'NOTES';
    public const TYPE_HISTORY = 'HISTORY';
    public const TYPE_PLAYER_PROFILE = 'PLAYER_PROFILE';

    /**
     * @param SessionInterface $session
     * @param ActionControllerInterface[] $actions
     * @param ViewControllerInterface[] $views
     */
    public function __construct(
        SessionInterface $session,
        array $actions,
        array $views
    ) {
        parent::__construct($session, '', '');

        foreach ($actions as $key => $action) {
            $this->addCallBack($key, $action, $action->performSessionCheck());
        }

        foreach ($views as $key => $view) {
            $this->addView($key, $view);
        }
    }
}
