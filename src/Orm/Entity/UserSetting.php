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
    #[Column(type: 'integer')]
    private int $user_id;

    #[Id]
    #[Column(type: 'string', enumType: UserSettingEnum::class)]
    private UserSettingEnum $setting;

    #[Column(type: 'string')]
    private string $value = '';

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function setUser(UserInterface $user): UserSettingInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

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
