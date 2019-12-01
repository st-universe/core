<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class PlayerDefaultsCreator implements PlayerDefaultsCreatorInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->researchedRepository = $researchedRepository;
    }

    public function createDefault(UserInterface $player): void
    {
        // Create default pm categories
        foreach (PrivateMessageFolderSpecialEnum::DEFAULT_CATEGORIES as $categoryId => $label) {
            $cat = $this->privateMessageFolderRepository->prototype();
            $cat->setUser($player);
            $cat->setDescription(gettext($label));
            $cat->setSpecial($categoryId);
            $cat->setSort($categoryId);

            $this->privateMessageFolderRepository->save($cat);
        }

        $db = $this->researchedRepository->prototype();

        $db->setResearch($player->getFaction()->getStartResearch());
        $db->setUser($player);
        $db->setFinished(time());
        $db->setActive(0);

        $this->researchedRepository->save($db);
    }
}
