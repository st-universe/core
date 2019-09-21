<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Lib\AllianceMemberWrapper;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AllianceRepository")
 * @Table(
 *     name="stu_alliances",
 *     indexes={
 *     }
 * )
 **/
class Alliance implements AllianceInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="text") */
    private $description = '';

    /** @Column(type="string") */
    private $homepage = '';

    /** @Column(type="integer") */
    private $date = 0;

    /** @Column(type="smallint") */
    private $faction_id = 0;

    /** @Column(type="boolean") */
    private $accept_applications = false;

    /** @Column(type="string", length=32) */
    private $avatar = '';

    private $founder;

    private $successor;

    private $diplomatic;

    private $members;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AllianceInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): AllianceInterface
    {
        $this->description = $description;
        return $this;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): AllianceInterface
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): AllianceInterface
    {
        $this->date = $date;
        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $faction_id): AllianceInterface
    {
        $this->faction_id = $faction_id;
        return $this;
    }

    public function getAcceptApplications(): bool
    {
        return $this->accept_applications;
    }

    public function setAcceptApplications(bool $acceptApplications): AllianceInterface
    {
        $this->accept_applications = $acceptApplications;
        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): AllianceInterface
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getFullAvatarPath(): string
    {
        return AVATAR_ALLIANCE_PATH . "/" . $this->getAvatar() . ".png";
    }

    public function getFounder(): AllianceJobInterface
    {
        if ($this->founder === null) {
            // @todo refactor
            global $container;

            $this->founder = $container->get(AllianceJobRepositoryInterface::class)
                ->getSingleResultByAllianceAndType(
                    $this->getId(),
                    ALLIANCE_JOBS_FOUNDER
                );
        }
        return $this->founder;
    }

    public function getSuccessor(): ?AllianceJobInterface
    {
        if ($this->successor === null) {
            // @todo refactor
            global $container;

            $this->successor = $container->get(AllianceJobRepositoryInterface::class)
                ->getSingleResultByAllianceAndType(
                    $this->getId(),
                    ALLIANCE_JOBS_SUCCESSOR
                );
        }
        return $this->successor;
    }

    public function getDiplomatic(): ?AllianceJobInterface
    {
        if ($this->diplomatic === null) {
            // @todo refactor
            global $container;

            $this->diplomatic = $container->get(AllianceJobRepositoryInterface::class)
                ->getSingleResultByAllianceAndType(
                    $this->getId(),
                    ALLIANCE_JOBS_DIPLOMATIC
                );

        }
        return $this->diplomatic;
    }

    public function getMembers(): array
    {
        if ($this->members === null) {
            // @todo refactor
            global $container;
            $list = $container->get(UserRepositoryInterface::class)->getByAlliance($this->getId());

            foreach ($list as $user) {
                $this->members[$user->getId()] = new AllianceMemberWrapper($user, $this);
            }
        }
        return $this->members;
    }
}
