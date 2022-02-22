<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowPartnerSites;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PartnerSiteRepositoryInterface;

final class ShowPartnerSites implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PARTNER_SITES';

    private PartnerSiteRepositoryInterface $partnerSiteRepository;

    public function __construct(
        PartnerSiteRepositoryInterface $partnerSiteRepository
    ) {
        $this->partnerSiteRepository = $partnerSiteRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Partnerseiten - Star Trek Universe'));
        $game->setTemplateFile('html/index_partner_sites.xhtml');

        $game->setTemplateVar('PARTNERSITES', $this->partnerSiteRepository->getOrdered());
    }
}
