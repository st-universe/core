<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowPartnerSites;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

/**
 * Shows all partner site items from the config
 */
final class ShowPartnerSites implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PARTNER_SITES';

    public function __construct(private ConfigInterface $config) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle('Partnerseiten - Star Trek Universe');
        $game->setTemplateFile('html/index/partnerSites.twig');

        $game->setTemplateVar('PARTNERSITES', $this->getPartnerSites());
    }

    /**
     * @return array<array{name: string, description: string, url: string, banner_path: string}>
     */
    private function getPartnerSites(): array
    {
        $baseBannerPath = $this->config->get('partner_sites.banner_path');

        return array_map(
            function (array $item) use ($baseBannerPath): array {
                /** @var array{name: string, description: string, url: string, banner_path: string} $item */
                $item['banner_path'] = sprintf(
                    '/%s/%s.png',
                    $baseBannerPath,
                    $item['banner_path']
                );
                return $item;
            },
            $this->config->get('partner_sites.items', [])
        );
    }
}
