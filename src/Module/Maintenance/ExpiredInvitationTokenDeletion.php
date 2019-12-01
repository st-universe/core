<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use DateInterval;
use DateTime;
use Noodlehaus\ConfigInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;

final class ExpiredInvitationTokenDeletion implements MaintenanceHandlerInterface
{
    private UserInvitationRepositoryInterface $userInvitationRepository;

    private ConfigInterface $config;

    public function __construct(
        UserInvitationRepositoryInterface $userInvitationRepository,
        ConfigInterface $config
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->config = $config;
    }

    public function handle(): void
    {
        $interval = new DateInterval(
            sprintf(
                'PT%dS',
                $this->config->get('game.invitation.ttl')
            )
        );

        $this->userInvitationRepository->truncateExpiredTokens(
            (new DateTime())->sub($interval)
        );
    }
}
