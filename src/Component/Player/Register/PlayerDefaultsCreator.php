<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;

final class PlayerDefaultsCreator implements PlayerDefaultsCreatorInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private LayerRepositoryInterface $layerRepository;

    private UserLayerRepositoryInterface $userLayerRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        ResearchedRepositoryInterface $researchedRepository,
        LayerRepositoryInterface $layerRepository,
        UserLayerRepositoryInterface $userLayerRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->researchedRepository = $researchedRepository;
        $this->layerRepository = $layerRepository;
        $this->userLayerRepository = $userLayerRepository;
    }

    public function createDefault(UserInterface $user): void
    {
        $this->createDefaultPmCategories($user);
        $this->createDefaultUserLayer($user);
        $this->createDefaultStartResearch($user);
    }

    private function createDefaultPmCategories(UserInterface $user): void
    {
        foreach (PrivateMessageFolderSpecialEnum::DEFAULT_CATEGORIES as $categoryId => $label) {
            $cat = $this->privateMessageFolderRepository->prototype();
            $cat->setUser($user);
            $cat->setDescription(gettext($label));
            $cat->setSpecial($categoryId);
            $cat->setSort($categoryId);

            $this->privateMessageFolderRepository->save($cat);
        }
    }

    private function createDefaultUserLayer(UserInterface $user): void
    {
        $defaultLayer = $this->layerRepository->find(MapEnum::DEFAULT_LAYER);

        if ($defaultLayer === null) {
            throw new RuntimeException('the default layer should be available');
        }

        $userLayer = $this->userLayerRepository->prototype();
        $userLayer->setLayer($defaultLayer);
        $userLayer->setUser($user);

        $this->userLayerRepository->save($userLayer);
        $user->getUserLayers()->set(MapEnum::DEFAULT_LAYER, $userLayer);
    }

    private function createDefaultStartResearch(UserInterface $user): void
    {
        $faction = $user->getFaction();
        $startResarch = $faction->getStartResearch();
        if ($startResarch === null) {
            return;
        }

        $db = $this->researchedRepository->prototype();

        $db->setResearch($startResarch);
        $db->setUser($user);
        $db->setFinished(time());
        $db->setActive(0);

        $this->researchedRepository->save($db);
    }
}
