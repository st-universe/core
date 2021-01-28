<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\FlightRanking;

use DatabaseTopListFlights;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class FlightRanking implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TOP_FLIGHTS';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die Top 10 der Vielflieger')
        );
        $game->setPageTitle(_('/ Datenbank / Die Top 10 der Vielflieger'));
        $game->showMacro('html/database.xhtml/top_flights_user');

        $game->setTemplateVar('FLIGHTS_LIST', $this->getTop10());
    }

    private function getTop10()
    {
        return array_map(
            function (array $data): DatabaseTopListFlights {
                return new DatabaseTopListFlights($data);
            },
            $this->flightSignatureRepository->getFlightsTop10()
        );
    }
}
