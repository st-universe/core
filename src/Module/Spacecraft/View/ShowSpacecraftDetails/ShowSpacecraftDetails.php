<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraftDetails;

use Override;
use request;
use Stu\Component\Spacecraft\System\Type\UplinkShipSystem;
use Stu\Lib\Trait\SpacecraftTractorPayloadTrait;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowSpacecraftDetails implements ViewControllerInterface
{
    use SpacecraftTractorPayloadTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFTDETAILS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TroopTransferUtilityInterface $troopTransferUtility
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle('Schiffsinformationen');
        $game->setMacroInAjaxWindow('html/spacecraft/spacecraftDetails.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar('TRACTOR_PAYLOAD', $this->getTractorPayload($wrapper->get()));

        $game->setTemplateVar('FOREIGNER_COUNT', $this->troopTransferUtility->foreignerCount($wrapper->get()));
        $game->setTemplateVar('MAX_FOREIGNERS', UplinkShipSystem::MAX_FOREIGNERS);
    }
}
