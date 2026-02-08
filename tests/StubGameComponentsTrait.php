<?php

namespace Stu;

use Stu\Module\Game\Component\GameComponentEnum;

trait StubGameComponentsTrait
{
    public function stubGameComponents(): void
    {
        StuMocks::get()->registerStubbedComponent(GameComponentEnum::COLONIES)
            ->registerStubbedComponent(GameComponentEnum::NAVIGATION)
            ->registerStubbedComponent(GameComponentEnum::PM)
            ->registerStubbedComponent(GameComponentEnum::NAGUS)
            ->registerStubbedComponent(GameComponentEnum::RESEARCH)
            ->registerStubbedComponent(GameComponentEnum::SERVERTIME_AND_VERSION)
            ->registerStubbedComponent(GameComponentEnum::USER);
    }
}
