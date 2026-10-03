<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RenameCrewman;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\View\ShowCrewmanDetails\ShowCrewmanDetails;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class RenameCrewman implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RENAME_CREWMAN';

    public function __construct(private CrewRepositoryInterface $crewRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowCrewmanDetails::VIEW_IDENTIFIER);

        $crew = $this->crewRepository->find(request::indInt('id'));
        if ($crew === null || $crew->getUser()->getId() !== $context->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $name = request::postString('name');
        if ($name === false) {
            $context->getInfo()->addInformation('Der Crewmanname darf nicht leer sein');
            return;
        }

        $name = trim($name);
        if ($name === '') {
            $context->getInfo()->addInformation('Der Crewmanname darf nicht leer sein');
            return;
        }

        $crew->setName($name);
        $this->crewRepository->save($crew);
        $context->getInfo()->addInformation('Der Crewmanname wurde geändert');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
