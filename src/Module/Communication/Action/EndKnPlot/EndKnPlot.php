<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EndKnPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_END_PLOT';

    public function __construct(private EndKnPlotRequestInterface $endKnPlotRequest, private RpgPlotRepositoryInterface $rpgPlotRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $plot = $this->rpgPlotRepository->find($this->endKnPlotRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $context->getUser()->getId()) {
            return;
        }
        if (!$plot->isActive()) {
            return;
        }
        $plot->setEndDate(time());

        $this->rpgPlotRepository->save($plot);

        $context->getInfo()->addInformation(_('Der Plot wurde beendet'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
