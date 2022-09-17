<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameTurnStatsRepository")
 * @Table(
 *     name="stu_game_turn_stats"
 * )
 **/
class GameTurnStats implements GameTurnStatsInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $user_count;

    /** @Column(type="integer") * */
    private $logins_24h;

    /** @Column(type="integer") * */
    private $vacation_count;

    /** @Column(type="integer") * */
    private $ship_count;

    /** @Column(type="integer") * */
    private $kn_count;

    /** @Column(type="integer") * */
    private $flight_sig_24h;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserCount(): int
    {
        return $this->user_count;
    }

    public function setUserCount(int $userCount): GameTurnStatsInterface
    {
        $this->user_count = $userCount;

        return $this;
    }

    public function getLogins24h(): int
    {
        return $this->logins_24h;
    }

    public function setLogins24h(int $logins24h): GameTurnStatsInterface
    {
        $this->logins_24h = $logins24h;

        return $this;
    }

    public function getVacationCount(): int
    {
        return $this->vacation_count;
    }

    public function setVacationCount(int $vacationCount): GameTurnStatsInterface
    {
        $this->vacation_count = $vacationCount;

        return $this;
    }

    public function getShipCount(): int
    {
        return $this->ship_count;
    }

    public function setShipCount(int $shipCount): GameTurnStatsInterface
    {
        $this->ship_count = $shipCount;

        return $this;
    }

    public function getKnCount(): int
    {
        return $this->kn_count;
    }

    public function setKnCount(int $knCount): GameTurnStatsInterface
    {
        $this->kn_count = $knCount;

        return $this;
    }

    public function getFlightSig24h(): int
    {
        return $this->flight_sig_24h;
    }

    public function setFlightSig24h(int $flightSig24h): GameTurnStatsInterface
    {
        $this->flight_sig_24h = $flightSig24h;

        return $this;
    }
}
