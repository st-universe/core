<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class PlayerDefaultsCreator implements PlayerDefaultsCreatorInterface
{
    private const START_TECH_LIST = [
        FACTION_FEDERATION => RESEARCH_START_FEDERATION,
        FACTION_ROMULAN => RESEARCH_START_ROMULAN,
        FACTION_KLINGON => RESEARCH_START_KLINGON,
        FACTION_CARDASSIAN => RESEARCH_START_CARDASSIAN,
        FACTION_FERENGI => RESEARCH_START_FERENGI,
    ];

    private $privateMessageFolderRepository;

    private $researchRepository;

    private $researchedRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        ResearchRepositoryInterface $researchRepository,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->researchRepository = $researchRepository;
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

        $startTechId = static::START_TECH_LIST[$player->getFaction()->getId()];

        /**
         * @var ResearchInterface $research
         */
        $research = $this->researchRepository->find($startTechId);

        $db = $this->researchedRepository->prototype();

        $db->setResearch($research);
        $db->setUser($player);
        $db->setFinished(time());
        $db->setActive(0);

        $this->researchedRepository->save($db);
    }
}