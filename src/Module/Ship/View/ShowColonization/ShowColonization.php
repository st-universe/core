<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowColonization;

use Override;
use request;

use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\ShipRumpSpecialAbilityEnum;

final class ShowColonization implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONIZATION';

    //5 months
    public const int USER_COLONIZATION_TIME = 12_960_000;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ColonizationCheckerInterface $colonizationChecker
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle("Kolonie gründen");
        $game->setMacroInAjaxWindow('');

        $colony = $ship->isOverColony();
        if ($colony === null) {
            return;
        }

        $layer = $colony->getSystem()->getLayer();
        $userColonies = $game->getUser()->getColonies();
        $colocount = count($userColonies);

        if ($layer) {
            if ($layer->isNoobzone()) {
                if ($game->getUser()->getRegistration()->getCreationDate() < time() - self::USER_COLONIZATION_TIME) {
                    $game->addInformation(sprintf(_('Im %s kann man nur eine Kolonie gründen <br>solang das Siedlerpatent nicht älter als 5 Monate ist. <br>Such dir eine Kolonie in einem anderen Sektor'), $layer->getName()));
                    return;
                }
                if ($colocount >= 4) {
                    $game->addInformation(sprintf(_('Im %s können nur maximal 4 Kolonien gegründet werden.<br>Such dir eine Kolonie in einem anderen Sektor'), $layer->getName()));
                    return;
                }
            }
        }

        if ($ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            if (!$this->colonizationChecker->canColonize($game->getUser(), $colony)) {
                return;
            }
        } else {
            return;
        }

        $game->setPageTitle(_('Kolonie gründen'));
        $game->setMacroInAjaxWindow('html/ship/colonization.twig');

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
