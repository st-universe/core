<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeBorgAssimilation;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Game\Lib\BorgAssimilation;

final class ChangeBorgAssimilation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_BORG_ASSIMILATION';

    public function __construct(private readonly BorgAssimilation $assimilation) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $mode = request::postString(self::ACTION_IDENTIFIER);
        if ($mode === 'disable') {
            $this->assimilation->deassimilate($context->getUser());
            $context->getInfo()->addInformation(_('Deassimilierung abgeschlossen'));
        } elseif ($mode === 'enable') {
            $this->assimilation->reassimilate($context->getUser());
            $context->setTemplateVar('BORG_ASSIMILATION_RESTARTED', true);
            $context->getInfo()->addInformation(_('Sie werden assimiliert werden! Wiederstand ist zwecklos!'));
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
