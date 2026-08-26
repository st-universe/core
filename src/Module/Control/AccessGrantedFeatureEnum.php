<?php

namespace Stu\Module\Control;

enum AccessGrantedFeatureEnum: string
{
    case COLONY_SANDBOX = 'COLONY_SANDBOX';
    case CREW_RACE_MODERATION = 'CREW_RACE_MODERATION';
}
