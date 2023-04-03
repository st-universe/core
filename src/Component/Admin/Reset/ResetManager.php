<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Throwable;

final class ResetManager implements ResetManagerInterface
{
    private PlayerDeletionInterface $playerDeletion;

    private ColonyRepositoryInterface $colonyRepository;

    private KnPostRepositoryInterface $knPostRepository;

    private HistoryRepositoryInterface $historyRepository;

    private GameTurnRepositoryInterface $gameTurnRepository;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private EntityManagerInterface $entityManager;

    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config,
        PlayerDeletionInterface $playerDeletion,
        ColonyRepositoryInterface $colonyRepository,
        KnPostRepositoryInterface $knPostRepository,
        HistoryRepositoryInterface $historyRepository,
        GameTurnRepositoryInterface $gameTurnRepository,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->playerDeletion = $playerDeletion;
        $this->colonyRepository = $colonyRepository;
        $this->knPostRepository = $knPostRepository;
        $this->historyRepository = $historyRepository;
        $this->gameTurnRepository = $gameTurnRepository;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->entityManager = $entityManager;
        $this->config = $config;
    }

    public function performReset(): void
    {
        $this->entityManager->beginTransaction();

        try {
            //$this->playerDeletion->handleReset();

            $this->entityManager->flush();

            $this->resetColonySurfaceMasks();
            //$this->deleteKnPostings();
            //$this->deleteKnPlotMembers();
            //$this->deleteKnPlots();
            $this->deleteHistory();
            $this->resetGameTurns();

            // clear game data
            // flight signatures
            // user locks
            // blocked users
        } catch (Throwable $t) {
            $this->entityManager->rollback();

            throw $t;
        }

        $this->entityManager->getConnection()->executeQuery(
            sprintf(
                'ALTER SEQUENCE stu_user_id_seq RESTART WITH %d',
                (int) $this->config->get('game.admin.id')
            )
        );

        $this->entityManager->commit();
    }

    /**
     * Resets the saved colony surface mask
     */
    private function resetColonySurfaceMasks(): void
    {
        foreach ($this->colonyRepository->findAll() as $colony) {
            $colony->setMask(null);

            $this->planetFieldRepository->truncateByColony($colony);

            $this->colonyRepository->save($colony);
        }
    }

    /**
     * Deletes all kn postings
     */
    private function deleteKnPostings(): void
    {
        foreach ($this->knPostRepository->findAll() as $knPost) {
            $this->knPostRepository->delete($knPost);
        }
    }

    /**
     * Deletes all history entries
     */
    private function deleteHistory(): void
    {
        foreach ($this->historyRepository->findAll() as $entry) {
            $this->historyRepository->delete($entry);
        }
    }

    /**
     * Deletes all rpg plot members
     */
    private function deleteKnPlotMembers(): void
    {
        foreach ($this->rpgPlotMemberRepository->findAll() as $plotMember) {
            $this->rpgPlotMemberRepository->delete($plotMember);
        }
    }

    /**
     * Deletes all rpg plots
     */
    private function deleteKnPlots(): void
    {
        foreach ($this->rpgPlotRepository->findAll() as $plot) {
            //echo "plot:" . $plot->getId() . ", memberCount:" . $plot->getMemberCount() . "\n";
            /* 
             if ($plot->getMemberCount() > 0) {
                foreach ($plot->getMembers() as $member) {
                    //echo "\tmember:" . $member->getUser()->getId() . "\n";
                }
            }
             */
            $this->rpgPlotRepository->delete($plot);
        }
    }

    /**
     * Deletes all existing game turns and starts over
     */
    private function resetGameTurns(): void
    {
        foreach ($this->gameTurnRepository->findAll() as $turn) {
            $this->gameTurnRepository->delete($turn);
        }

        $turn = $this->gameTurnRepository->prototype()
            ->setTurn(1)
            ->setStart(time())
            ->setEnd(0);

        $this->gameTurnRepository->save($turn);
    }
}
