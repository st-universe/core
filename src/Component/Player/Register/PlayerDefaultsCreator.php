<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Override;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;


final class PlayerDefaultsCreator implements PlayerDefaultsCreatorInterface
{
    public function __construct(private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private ResearchedRepositoryInterface $researchedRepository, private LayerRepositoryInterface $layerRepository, private UserLayerRepositoryInterface $userLayerRepository,  private TutorialStepRepositoryInterface $tutorialStepRepository,  private UserTutorialRepositoryInterface $userTutorialRepository) {}

    #[Override]
    public function createDefault(UserInterface $user): void
    {
        $this->createDefaultPmCategories($user);
        $this->createDefaultUserLayer($user);
        $this->createDefaultStartResearch($user);
        $this->createTutorialsForPlayer($user);
    }

    private function createDefaultPmCategories(UserInterface $user): void
    {
        foreach (PrivateMessageFolderTypeEnum::cases() as $folderType) {

            if (!$folderType->isDefault()) {
                continue;
            }

            $cat = $this->privateMessageFolderRepository->prototype();
            $cat->setUser($user);
            $cat->setDescription(gettext($folderType->getDescription()));
            $cat->setSpecial($folderType);
            $cat->setSort($folderType->value);

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

    private function createTutorialsForPlayer(UserInterface $player): void
    {
        $firstSteps = $this->tutorialStepRepository->findAllFirstSteps();
        foreach ($firstSteps as $step) {
            $userTutorial = $this->userTutorialRepository->prototype();
            $userTutorial->setUser($player);
            $userTutorial->setTutorialStep($step);

            $this->userTutorialRepository->save($userTutorial);
        }
    }
}
