<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Repository\UserSettingRepository;

#[Table(name: 'stu_user_setting')]
#[Entity(repositoryClass: UserSettingRepository::class)]
class UserSetting
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[Column(type: 'string', enumType: UserSettingEnum::class)]
    private UserSettingEnum $setting;

    #[Column(type: 'string')]
    private string $value = '';

    public function setUser(User $user): UserSetting
    {
        $this->user = $user;

        return $this;
    }

    public function setSetting(UserSettingEnum $setting): UserSetting
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): UserSetting
    {
        $this->value = $value;

        return $this;
    }
}
