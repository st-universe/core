<?php

namespace Stu\PlanetGenerator;

use Exception;

final class PlanetGenerator implements PlanetGeneratorInterface
{

    public const COLGEN_BASEWEIGHT = 'baseweight';
    public const COLGEN_WEIGHT = 'weight';
    public const COLGEN_DETAILS = 'details';
    public const COLGEN_SIZEW = 'sizew';
    public const COLGEN_BASEFIELD = 'basefield';
    public const COLGEN_TO = 'to';
    public const COLGEN_SIZEH = 'sizeh';
    public const COLGEN_MODE = 'mode';
    public const COLGEN_DESCRIPTION = 'description';
    public const COLGEN_NUM = 'num';
    public const COLGEN_FROM = 'from';
    public const COLGEN_ADJACENT = 'adjacent';
    public const COLGEN_NOADJACENT = 'noadjacent';
    public const COLGEN_NOADJACENTLIMIT = 'noadjacentlimit';
    public const COLGEN_FRAGMENTATION = 'fragmentation';
    public const COLGEN_TYPE = 'type';
    public const COLGEN_Y = 'y';
    public const COLGEN_X = 'x';
    public const COLGEN_W = 'w';
    public const BONUS_ANYFOOD = 1;
    public const BONUS_LANDFOOD = 2;
    public const BONUS_WATERFOOD = 3;
    public const BONUS_HABITAT = 10;
    public const BONUS_ANYRESOURCE = 20;
    public const BONUS_ORE = 22;
    public const BONUS_DEUTERIUM = 21;
    public const BONUS_AENERGY = 30;
    public const BONUS_SENERGY = 31;
    public const BONUS_WENERGY = 32;
    public const BONUS_SUPER = 99;

    private function weightedDraw($a, $fragmentation = 0)
    {
        for ($i = 0; $i < count($a); $i++) {
            $a[$i][self::COLGEN_WEIGHT] = rand(1, ceil($a[$i][self::COLGEN_BASEWEIGHT] + $fragmentation));
        }
        usort($a, function ($a, $b) {
            if ($a[self::COLGEN_WEIGHT] < $b[self::COLGEN_WEIGHT]) {
                return +1;
            }
            if ($a[self::COLGEN_WEIGHT] > $b[self::COLGEN_WEIGHT]) {
                return -1;
            }
            return (rand(1, 3) - 2);
        });

        return $a[0];
    }

    private function shadd($arr, $fld, $bonus)
    {

        array_push($arr[self::COLGEN_FROM], $fld);
        array_push($arr[self::COLGEN_TO], $fld . $bonus);

        return $arr;
    }

    private function getBonusFieldTransformations($btype)
    {
        $res = array();
        $res[self::COLGEN_FROM] = array();
        $res[self::COLGEN_TO] = array();

        if (($btype == self::BONUS_LANDFOOD) || ($btype == self::BONUS_ANYFOOD)) {
            $res = $this->shadd($res, 101, "01");
            $res = $this->shadd($res, 111, "01");
            $res = $this->shadd($res, 112, "01");
            $res = $this->shadd($res, 121, "01");
            $res = $this->shadd($res, 601, "01");
            $res = $this->shadd($res, 611, "01");
            $res = $this->shadd($res, 602, "01");
        }
        if (($btype == self::BONUS_WATERFOOD) || ($btype == self::BONUS_ANYFOOD)) {
            $res = $this->shadd($res, 201, "02");
            $res = $this->shadd($res, 211, "02");
            $res = $this->shadd($res, 221, "02");
        }
        if ($btype == self::BONUS_HABITAT) {
            $res = $this->shadd($res, 101, "03");
            $res = $this->shadd($res, 111, "03");
            $res = $this->shadd($res, 112, "03");
            $res = $this->shadd($res, 601, "03");
            $res = $this->shadd($res, 601, "04");
            $res = $this->shadd($res, 602, "03");
            $res = $this->shadd($res, 611, "03");
            $res = $this->shadd($res, 611, "04");
            $res = $this->shadd($res, 713, "04");
            $res = $this->shadd($res, 715, "04");
            $res = $this->shadd($res, 725, "04");
        }

        // solar
        if (($btype == self::BONUS_SENERGY) || ($btype == self::BONUS_AENERGY)) {
            $res = $this->shadd($res, 401, "31");
            $res = $this->shadd($res, 402, "31");
            $res = $this->shadd($res, 403, "31");
            $res = $this->shadd($res, 404, "31");
            $res = $this->shadd($res, 713, "31");
        }

        // strömung
        if (($btype == self::BONUS_WENERGY) || ($btype == self::BONUS_AENERGY)) {
            $res = $this->shadd($res, 201, "32");
            $res = $this->shadd($res, 221, "32");
        }

        if (($btype == self::BONUS_ORE) || ($btype == self::BONUS_ANYRESOURCE)) {
            $res = $this->shadd($res, 701, "12");
            $res = $this->shadd($res, 702, "12");
            $res = $this->shadd($res, 703, "12");
            $res = $this->shadd($res, 704, "12");
            $res = $this->shadd($res, 705, "12");
            $res = $this->shadd($res, 706, "12");
        }

        if (($btype == self::BONUS_DEUTERIUM) || ($btype == self::BONUS_ANYRESOURCE)) {
            $res = $this->shadd($res, 201, "11");
            $res = $this->shadd($res, 210, "11");
            $res = $this->shadd($res, 211, "11");
            $res = $this->shadd($res, 221, "11");
            $res = $this->shadd($res, 501, "11");
            $res = $this->shadd($res, 511, "11");
        }

        if ($btype == self::BONUS_SUPER) {

            // dili
            $res = $this->shadd($res, 701, "21");
            $res = $this->shadd($res, 702, "21");
            $res = $this->shadd($res, 703, "21");
            $res = $this->shadd($res, 704, "21");
            $res = $this->shadd($res, 705, "21");
            $res = $this->shadd($res, 706, "21");

            // trit
            $res = $this->shadd($res, 701, "22");
            $res = $this->shadd($res, 702, "22");
            $res = $this->shadd($res, 703, "22");
            $res = $this->shadd($res, 704, "22");
            $res = $this->shadd($res, 705, "22");
            $res = $this->shadd($res, 706, "22");
        }

        return $res;
    }

    private function createBonusPhase($btype)
    {

        $bphase = array();

        $bphase[self::COLGEN_MODE] = "nocluster";
        $bphase[self::COLGEN_DESCRIPTION] = "Bonusfeld";

        $br = $this->getBonusFieldTransformations($btype);

        $bphase[self::COLGEN_NUM] = 1;
        $bphase[self::COLGEN_FROM] = $br[self::COLGEN_FROM];
        $bphase[self::COLGEN_TO] = $br[self::COLGEN_TO];

        $bphase[self::COLGEN_ADJACENT] = 0;
        $bphase[self::COLGEN_NOADJACENT] = 0;
        $bphase[self::COLGEN_NOADJACENTLIMIT] = 0;
        $bphase[self::COLGEN_FRAGMENTATION] = 100;

        return $bphase;
    }

    private function getWeightingList($colfields, $mode, $from, $to, $adjacent, $no_adjacent, $noadjacentlimit = 0): ?array
    {
        $res = null;

        $w = $colfields[self::COLGEN_W]; //count($colfields);
        $h = count($colfields[0] ?? []);
        $c = 0;
        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $skip = 1;
                for ($k = 0; $k < count($from); $k++) {
                    if ($colfields[$j][$i] == $from[$k]) {
                        $skip = 0;
                    }
                }
                if ($skip == 1) {
                    continue;
                }

                $bw = 1;
                if ((($mode == "polar") || ($mode == "strict polar")) && ($i == 0 || $i == $h - 1)) {
                    $bw += 1;
                }
                if (($mode == "polar seeding north") && ($i == 0)) {
                    $bw += 2;
                }
                if (($mode == "polar seeding south") && ($i == $h - 1)) {
                    $bw += 2;
                }

                if (($mode == "equatorial") && (($i == 2 && $h == 5) || (($i == 2 || $i == 3) && $h == 6))) {
                    $bw += 1;
                }

                if ($mode != "nocluster" && $mode != "forced adjacency" && $mode != "forced rim" && $mode != "polar seeding north" && $mode != "polar seeding south") {
                    for ($k = 0; $k < count($to); $k++) {
                        if ($colfields[$j - 1][$i] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j + 1][$i] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j][$i - 1] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j][$i + 1] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j - 1][$i - 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j + 1][$i + 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j + 1][$i - 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j - 1][$i + 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                    }
                }

                if ((($mode == "polar seeding north") && ($i == 0)) || (($mode == "polar seeding south") && ($i == $h - 1))) {
                    for ($k = 0; $k < count($to); $k++) {
                        if ($colfields[$j - 1][$i] == $to[$k]) {
                            $bw += 2;
                        }
                        if ($colfields[$j + 1][$i] == $to[$k]) {
                            $bw += 2;
                        }
                    }
                }

                if ($adjacent[0]) {
                    for ($k = 0; $k < count($adjacent); $k++) {
                        if ($colfields[$j - 1][$i] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j + 1][$i] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j][$i - 1] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j][$i + 1] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($colfields[$j - 1][$i - 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j + 1][$i + 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j + 1][$i - 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($colfields[$j - 1][$i + 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                    }
                }

                if ($no_adjacent[0]) {
                    for ($k = 0; $k < count($no_adjacent); $k++) {
                        $ad = 0;
                        if ($colfields[$j - 1][$i] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($colfields[$j + 1][$i] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($colfields[$j][$i - 1] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($colfields[$j][$i + 1] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($colfields[$j - 1][$i - 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($colfields[$j + 1][$i + 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($colfields[$j + 1][$i - 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($colfields[$j - 1][$i + 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }

                        if ($ad > $noadjacentlimit) {
                            $bw = 0;
                        }
                    }
                }

                if (($mode == "forced adjacency") && ($bw < 2)) {
                    $bw = 0;
                }
                if (($mode == "forced rim") && ($bw < 1.5)) {
                    $bw = 0;
                }

                if (($mode == "polar") && ($i > 1) && ($i < $h - 2)) {
                    $bw = 0;
                }
                if (($mode == "strict polar") && ($i > 0) && ($i < $h - 1)) {
                    $bw = 0;
                }
                if ($mode == "polar seeding north" && ($i > 1)) {
                    $bw = 0;
                }
                if ($mode == "polar seeding south" && ($i < $h - 2)) {
                    $bw = 0;
                }
                if (($mode == "equatorial") && (($i < 2) || ($i > 3)) && ($h == 6)) {
                    $bw = 0;
                }
                if (($mode == "equatorial") && (($i < 2) || ($i > 3)) && ($h == 5)) {
                    $bw = 0;
                }

                if (($mode == "lower orbit") && ($i != 1)) {
                    $bw = 0;
                }
                if (($mode == "upper orbit") && ($i != 0)) {
                    $bw = 0;
                }

                if (($mode == "tidal seeding") && ($j != 0)) {
                    $bw = 0;
                }

                if (($mode == "right") && ($colfields[$j - 1][$i] != $adjacent[0])) {
                    $bw = 0;
                }
                if (($mode == "below") && ($colfields[$j][$i - 1] != $adjacent[0])) {
                    $bw = 0;
                }
                if (($mode == "crater seeding") && (($j == $w - 1) || ($i == $h - 1))) {
                    $bw = 0;
                }

                if ($bw > 0) {
                    $res[$c][self::COLGEN_X] = $j;
                    $res[$c][self::COLGEN_Y] = $i;
                    $res[$c][self::COLGEN_BASEWEIGHT] = $bw;
                    $c++;
                }
            }
        }
        return $res;
    }

    private function doPhase($p, $phase, $colfields)
    {
        if ($phase[$p][self::COLGEN_MODE] == "fullsurface") {
            $k = 0;
            for ($ih = 0; $ih < $colfields[self::COLGEN_Y]; $ih++) {
                for ($iw = 0; $iw < $colfields[self::COLGEN_W]; $iw++) {

                    $k++;

                    $colfields[$iw][$ih] = $phase[$p][self::COLGEN_TYPE] * 100 + $k;
                }
            }
        } else {
            for ($i = 0; $i < $phase[$p][self::COLGEN_NUM]; $i++) {
                $arr = $this->getWeightingList(
                    $colfields,
                    $phase[$p][self::COLGEN_MODE],
                    $phase[$p][self::COLGEN_FROM],
                    $phase[$p][self::COLGEN_TO],
                    $phase[$p][self::COLGEN_ADJACENT],
                    $phase[$p][self::COLGEN_NOADJACENT],
                    $phase[$p][self::COLGEN_NOADJACENTLIMIT]
                );
                if ($arr === null || count($arr) == 0) {
                    break;
                }

                $field = $this->weightedDraw($arr, $phase[$p][self::COLGEN_FRAGMENTATION]);
                $ftype = $colfields[$field[self::COLGEN_X]][$field[self::COLGEN_Y]];

                $t = 0;
                unset($ta);
                for ($c = 0; $c < count($phase[$p][self::COLGEN_FROM]); $c++) {

                    if ($ftype == $phase[$p][self::COLGEN_FROM][$c]) {
                        $ta[$t] = $phase[$p][self::COLGEN_TO][$c];
                        $t++;
                    }
                }
                if ($t > 0) {
                    $colfields[$field[self::COLGEN_X]][$field[self::COLGEN_Y]] = $ta[rand(0, $t - 1)];
                }
            }
        }
        return $colfields;
    }

    private function combine($col, $orb, $gnd)
    {

        $q = 0;
        for ($i = 0; $i < $orb[self::COLGEN_Y]; $i++) {
            for ($j = 0; $j < $orb[self::COLGEN_W]; $j++) {
                $res[$q] = $orb[$j][$i];
                $q++;
            }
        }

        for ($i = 0; $i < $col[self::COLGEN_Y]; $i++) {
            for ($j = 0; $j < $col[self::COLGEN_W]; $j++) {
                $res[$q] = $col[$j][$i];
                $q++;
            }
        }

        for ($i = 0; $i < $gnd[self::COLGEN_Y]; $i++) {
            for ($j = 0; $j < $gnd[self::COLGEN_W]; $j++) {
                $res[$q] = $gnd[$j][$i];
                $q++;
            }
        }

        return $res;
    }

    private function loadPlanetType(int $id): array
    {
        $fileName = sprintf(
            '%s/coldata/%d.php',
            __DIR__,
            $id
        );
        if (!file_exists($fileName)) {
            throw new Exception('Planetgenerator description file missing for id ' . $id);
        }
        $requireResult = require $fileName;

        if (is_bool($requireResult)) {
            throw new Exception('Error loading planetgenerator description file for id ' . $id);
        }

        if (is_int($requireResult)) {
            throw new Exception('Error loading planetgenerator description file for id ' . $id);
        }

        if (is_int($requireResult)) {
            throw new Exception('Error loading planetgenerator description file for id ' . $id);
        }

        return $requireResult;
    }

    public function generateColony(int $id, int $bonusfields = 2): array
    {

        $bonusdata = array();

        list($odata, $data, $udata, $ophase, $phase, $uphase, $ophases, $phases, $uphases, $hasground) = $this->loadPlanetType($id);

        // start bonus

        if ($data[self::COLGEN_SIZEW] != 10) {
            $bonusfields = $bonusfields - 1;
        }

        $bftaken = 0;
        $phasesSuper = 0;
        $phasesOre = 0;
        $phasesDeut = 0;
        $phasesResource = 0;
        $phasesOther = 0;

        if (($bftaken < $bonusfields) && (rand(1, 100) <= 15)) {
            $phasesSuper += 1;
            $bftaken += 1;
        }
        if (($bftaken < $bonusfields) && (rand(1, 100) <= 80)) {
            $phasesResource += 1;
            $bftaken += 1;
        }
        if (($phasesSuper == 0) && ($data[self::COLGEN_SIZEW] > 7)) {
            if (($bftaken < $bonusfields) && (rand(1, 100) <= 10)) {
                $phasesResource += 1;
                $bftaken += 1;
            }
        }

        if ($bftaken < $bonusfields) {
            $restcount = $bonusfields - $bftaken;

            $phasesOther += $restcount;
            $bftaken += $restcount;
        }

        $bphases = 0;

        // Bonus Phases

        unset($taken);

        for ($i = 0; $i < $phasesSuper; $i++) {
            $bphase[$bphases] = $this->createBonusPhase(self::BONUS_SUPER);
            $bphases++;
        }

        for ($i = 0; $i < $phasesResource; $i++) {
            $bphase[$bphases] = $this->createBonusPhase(self::BONUS_ANYRESOURCE);
            $bphases++;
        }

        for ($i = 0; $i < $phasesDeut; $i++) {
            $bphase[$bphases] = $this->createBonusPhase(self::BONUS_DEUTERIUM);
            $bphases++;
        }

        for ($i = 0; $i < $phasesOre; $i++) {
            $bphase[$bphases] = $this->createBonusPhase(self::BONUS_ORE);
            $bphases++;
        }


        for ($i = 0; $i < $phasesOther; $i++) {
            if (count($bonusdata) == 0) {
                break;
            }

            shuffle($bonusdata);
            $next = array_shift($bonusdata);

            $bphase[$bphases] = $this->createBonusPhase($next);
            $bphases++;
        }

        // end bonus

        $log = "";

        $h = $data[self::COLGEN_SIZEH];
        $w = $data[self::COLGEN_SIZEW];

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $colfields[$j][$i] = $data[self::COLGEN_BASEFIELD];
            }
        }
        $colfields[self::COLGEN_Y] = $h;
        $colfields[self::COLGEN_W] = $w;

        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $orbfields[$j][$i] = $odata[self::COLGEN_BASEFIELD];
            }
        }
        $orbfields[self::COLGEN_Y] = 2;
        $orbfields[self::COLGEN_W] = $w;

        $gndfields[self::COLGEN_Y] = 0;
        if ($hasground) {
            for ($i = 0; $i < 2; $i++) {
                for ($j = 0; $j < $w; $j++) {
                    $gndfields[$j][$i] = $udata[self::COLGEN_BASEFIELD];
                }
            }
            $gndfields[self::COLGEN_Y] = 2;
            $gndfields[self::COLGEN_W] = $w;
        }

        for ($i = 0; $i < $phases; $i++) {
            $log = $log . "<br>" . $phase[$i][self::COLGEN_DESCRIPTION];

            $colfields = $this->doPhase($i, $phase, $colfields);
        }

        for ($i = 0; $i < $ophases; $i++) {
            $log = $log . "<br>" . $ophase[$i][self::COLGEN_DESCRIPTION];

            $orbfields = $this->doPhase($i, $ophase, $orbfields);
        }

        for ($i = 0; $i < $uphases; $i++) {
            $log = $log . "<br>" . $uphase[$i][self::COLGEN_DESCRIPTION];

            $gndfields = $this->doPhase($i, $uphase, $gndfields);
        }

        for ($i = 0; $i < $bphases; $i++) {
            $log = $log . "<br>" . $bphase[$i][self::COLGEN_DESCRIPTION];

            $colfields = $this->doPhase($i, $bphase, $colfields);
        }

        return $this->combine($colfields, $orbfields, $gndfields);
    }
}
