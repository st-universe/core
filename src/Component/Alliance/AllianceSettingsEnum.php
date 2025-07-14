<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

enum AllianceSettingsEnum: string
{
    case ALLIANCE_FOUNDER_DESCRIPTION = 'founder_description';
    case ALLIANCE_SUCCESSOR_DESCRIPTION = 'successor_description';
    case ALLIANCE_DIPLOMATIC_DESCRIPTION = 'diplomatic_description';
}
