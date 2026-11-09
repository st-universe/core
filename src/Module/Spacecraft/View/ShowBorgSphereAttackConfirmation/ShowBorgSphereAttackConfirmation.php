<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowBorgSphereAttackConfirmation;

use Override;
use request;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class ShowBorgSphereAttackConfirmation implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BORG_SPHERE_ATTACK_CONFIRMATION';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private BorgSphereRepositoryInterface $events,
        private SessionStorageInterface $session,
        private StuTime $time
    ) {}

    #[Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setPageTitle('Angriff bestätigen');
        $game->setMacroInAjaxWindow('');
        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            request::indInt('id'),
            $game->getUser()->getId(),
            request::indInt('target'),
            false,
            false
        );
        $source = $wrappers->getSource()->get();
        $target = $wrappers->getTarget()?->get();
        if ($target === null || !$this->isAvailable($source, $target)) {
            $game->getInfo()->addInformation('Diese Borg-Sphäre ist nicht mehr für diesen Angriff verfügbar');
            return;
        }
        $nonce = request::getString('borg_confirmation') ?: '';
        if (!$this->hasConfirmation($source, $target, $nonce)) {
            $game->getInfo()->addInformation('Diese Angriffsbestätigung ist ungültig oder abgelaufen. Starte den Angriff erneut');
            return;
        }
        $game->setTemplateVar('BORG_ATTACK_PARAMETERS', http_build_query([
            'id' => $source->getId(),
            'target' => $target->getId(),
            'sstr' => $game->getSessionString(),
            'borg_confirmation' => $nonce,
            'borg_target_system' => request::getString('borg_target_system')
        ]));
        $game->setMacroInAjaxWindow('html/ship/borgSphereAttackConfirmation.twig');
    }

    private function isAvailable(Spacecraft $source, Spacecraft $target): bool
    {
        $event = $this->events->getByShip($target);
        return $event !== null && !$event->isFinished()
            && $event->getConfiguration()->isActive($this->time->time())
            && !$target->getCondition()->isDestroyed() && $target->getCondition()->isDisabled()
            && $source->getLocation() === $target->getLocation();
    }

    private function hasConfirmation(Spacecraft $source, Spacecraft $target, string $nonce): bool
    {
        $stored = $this->session->getSessionValue(sprintf('borg_sphere_attack_%d_%d', $source->getId(), $target->getId()));
        return is_array($stored) && isset($stored['nonce'], $stored['expires'])
            && is_string($stored['nonce']) && is_int($stored['expires'])
            && $stored['expires'] >= $this->time->time() && hash_equals($stored['nonce'], $nonce);
    }
}
