<?php

declare(strict_types=1);

namespace Stu\Component\Player\Validation\Validators;

use DateTime;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\PlayerTagTypeEnum;
use Stu\Component\Player\Validation\PlayerValidationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserTagRepositoryInterface;
use Usox\IpIntel\Exception\IpIntelException;
use Usox\IpIntel\IpIntelInterface;

final class IpIntelValidator implements PlayerValidationInterface
{
    private IpIntelInterface $ipIntel;

    private UserIpTableRepositoryInterface $userIpTableRepository;

    private UserTagRepositoryInterface $userTagRepository;

    private ConfigInterface $config;

    public function __construct(
        IpIntelInterface $ipIntel,
        UserIpTableRepositoryInterface $userIpTableRepository,
        UserTagRepositoryInterface $userTagRepository,
        ConfigInterface $config
    ) {
        $this->ipIntel = $ipIntel;
        $this->userIpTableRepository = $userIpTableRepository;
        $this->userTagRepository = $userTagRepository;
        $this->config = $config;
    }

    public function validate(UserInterface $user): bool
    {
        $validationPropability = $this->config->get('security.validation.ip_intel_validation_propability');
        if ($validationPropability === 0) {
            return true;
        }

        $entry = $this->userIpTableRepository->findMostRecentByUser($user);

        if ($entry === null) {
            // should never happen, but...
            return true;
        }

        if (rand(1, $validationPropability) === 1) {
            try {
                $validationResult = $this->ipIntel->validate(
                    $entry->getIp(),
                    $this->config->get('security.validation.ip_intel_validation_score')
                );
            } catch (IpIntelException $e) {
                return true;
            }

            if ($validationResult === false) {
                $tag = $this->userTagRepository->prototype()
                    ->setUser($user)
                    ->setDate(new DateTime())
                    ->setTagTypeId(PlayerTagTypeEnum::FRAUD);

                $this->userTagRepository->save($tag);
            }

            // maybe fail directly in the future
        }

        return true;
    }
}
