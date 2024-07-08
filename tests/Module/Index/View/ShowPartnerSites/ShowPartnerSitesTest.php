<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowPartnerSites;

use Mockery;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class ShowPartnerSitesTest extends StuTestCase
{
    /** @var MockInterface&ConfigInterface */
    private MockInterface $config;

    private ShowPartnerSites $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new ShowPartnerSites(
            $this->config
        );
    }

    public function testHandleShowsSites(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $baseBannerPath = 'some-base-banner-path';
        $siteName = 'some-site';
        $siteDescription = 'some-fancy-site';
        $siteUrl = 'some-url';
        $siteBannerPath = 'some-banner-path';
        $computedBannerPath = sprintf(
            '/%s/%s.png',
            $baseBannerPath,
            $siteBannerPath
        );

        $game->shouldReceive('setPageTitle')
            ->with('Partnerseiten - Star Trek Universe')
            ->once();
        $game->shouldReceive('setTemplateFile')
            ->with('html/index/partnerSites.twig')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with(
                'PARTNERSITES',
                Mockery::on(
                    fn (array $items): bool =>
                    $items === [[
                        'name' => $siteName,
                        'description' => $siteDescription,
                        'url' => $siteUrl,
                        'banner_path' => $computedBannerPath,
                    ]]
                )
            )
            ->once();

        $this->config->shouldReceive('get')
            ->with('partner_sites.items', [])
            ->once()
            ->andReturn([[
                'name' => $siteName,
                'description' => $siteDescription,
                'url' => $siteUrl,
                'banner_path' => $siteBannerPath,
            ]]);
        $this->config->shouldReceive('get')
            ->with('partner_sites.banner_path')
            ->once()
            ->andReturn($baseBannerPath);

        $this->subject->handle($game);
    }
}
