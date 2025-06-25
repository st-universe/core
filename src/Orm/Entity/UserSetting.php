<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Repository\UserSettingRepository;

#[Table(name: 'stu_user_setting')]
#[Entity(repositoryClass: UserSettingRepository::class)]
class UserSetting implements UserSettingInterface
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Id]
    #[Column(type: 'string', enumType: UserSettingEnum::class)]
    private UserSettingEnum $setting;

    #[Column(type: 'string')]
    private string $value = '';

    #[Override]
    public function setUser(UserInterface $user): UserSettingInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function setSetting(UserSettingEnum $setting): UserSettingInterface
    {
        $this->setting = $setting;

        return $this;
    }

    #[Override]
    public function getValue(): string
    {
        return $this->value;
    }

    #[Override]
    public function setValue(string $value): UserSettingInterface
    {
        $this->value = $value;

        return $this;
    }
}
