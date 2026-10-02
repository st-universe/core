<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

use Stu\Exception\AccessViolationException;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class RenameBuildplan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILDPLAN_CHANGE_NAME';

    public function __construct(
        private RenameBuildplanRequestInterface $renameBuildplanRequest,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $context->setView(ShowModuleScreenBuildplan::VIEW_IDENTIFIER);

        $newName = CleanTextUtils::clearEmojis($this->renameBuildplanRequest->getNewName());
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 255) {
            $context->getInfo()->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if ($this->spacecraftBuildplanRepository->findByUserAndName($userId, $newName) !== null) {
            $context->getInfo()->addInformation(_('Ein Bauplan mit diesem Namen existiert bereits'));
            return;
        }

        $plan = $this->spacecraftBuildplanRepository->find($this->renameBuildplanRequest->getId());
        if ($plan === null || $plan->getUserId() !== $userId) {
            throw new AccessViolationException();
        }

        $plan->setName($newName);

        $this->spacecraftBuildplanRepository->save($plan);

        $context->getInfo()->addInformation(_('Der Name des Bauplans wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
