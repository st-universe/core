<?php

namespace Stu\PlanetGenerator;

use Exception;

final class PlanetGenerator implements PlanetGeneratorInterface
{
    //phase settings
    public const COLGEN_MODE = 'mode';
    public const COLGEN_DESCRIPTION = 'description';
    public const COLGEN_NUM = 'num';
    public const COLGEN_FROM = 'from';
    public const COLGEN_TO = 'to';
    public const COLGEN_ADJACENT = 'adjacent';
    public const COLGEN_NOADJACENT = 'noadjacent';
    public const COLGEN_NOADJACENTLIMIT = 'noadjacentlimit';
    public const COLGEN_FRAGMENTATION = 'fragmentation';

    //other
    public const COLGEN_BASEWEIGHT = 'baseweight';
    public const COLGEN_WEIGHT = 'weight';
    public const COLGEN_DETAILS = 'details';
    public const COLGEN_BASEFIELD = 'basefield';
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

    //phases enum
    private const PHASE_COLONY = 1;
    private const PHASE_ORBIT = 2;
    private const PHASE_UNDERGROUND = 3;
    private const PHASE_BONUS = 4;

    //config values
    public const CONFIG_COLGEN_SIZEW = 'sizew';
    public const CONFIG_COLGEN_SIZEH = 'sizeh';
    private const CONFIG_BASEFIELD_COLONY = 1;
    private const CONFIG_BASEFIELD_ORBIT = 2;
    private const CONFIG_BASEFIELD_UNDERGROUND = 3;

    public function generateColony(int $id, int $bonusfields = 2): array
    {
        $bonusdata = [];

        list($odata, $data, $udata, $ophase, $phase, $uphase, $hasground) = $this->loadPlanetType($id);

        $config = [
            self::CONFIG_COLGEN_SIZEW => $data[self::CONFIG_COLGEN_SIZEW],
            self::CONFIG_COLGEN_SIZEH => $data[self::CONFIG_COLGEN_SIZEH],
            self::CONFIG_BASEFIELD_COLONY => $data[self::COLGEN_BASEFIELD],
            self::CONFIG_BASEFIELD_ORBIT => $odata[self::COLGEN_BASEFIELD],
            self::CONFIG_BASEFIELD_UNDERGROUND => $udata[self::COLGEN_BASEFIELD]
        ];

        // start bonus
        if ($config[self::CONFIG_COLGEN_SIZEW] != 10) {
            $bonusfields = $bonusfields - 1;
        }

        $bftaken = 0;
        $phaseSuperCount = 0;
        $phasesOreCount = 0;
        $phasesDeutCount = 0;
        $phasesResourceCount = 0;
        $phasesOther = 0;

        if (($bftaken < $bonusfields) && (rand(1, 100) <= 15)) {
            $phaseSuperCount += 1;
            $bftaken += 1;
        }
        if (($bftaken < $bonusfields) && (rand(1, 100) <= 80)) {
            $phasesResourceCount += 1;
            $bftaken += 1;
        }
        if (($phaseSuperCount == 0) && ($config[self::CONFIG_COLGEN_SIZEW] > 7)) {
            if (($bftaken < $bonusfields) && (rand(1, 100) <= 10)) {
                $phasesResourceCount += 1;
                $bftaken += 1;
            }
        }

        if ($bftaken < $bonusfields) {
            $restcount = $bonusfields - $bftaken;

            $phasesOther += $restcount;
            $bftaken += $restcount;
        }

        $bonusPhaseCount = 0;

        // Bonus Phases

        unset($taken);
        $bphase = [];

        for ($i = 0; $i < $phaseSuperCount; $i++) {
            $bphase[$bonusPhaseCount] = $this->createBonusPhase(self::BONUS_SUPER);
            $bonusPhaseCount++;
        }

        for ($i = 0; $i < $phasesResourceCount; $i++) {
            $bphase[$bonusPhaseCount] = $this->createBonusPhase(self::BONUS_ANYRESOURCE);
            $bonusPhaseCount++;
        }

        for ($i = 0; $i < $phasesDeutCount; $i++) {
            $bphase[$bonusPhaseCount] = $this->createBonusPhase(self::BONUS_DEUTERIUM);
            $bonusPhaseCount++;
        }

        for ($i = 0; $i < $phasesOreCount; $i++) {
            $bphase[$bonusPhaseCount] = $this->createBonusPhase(self::BONUS_ORE);
            $bonusPhaseCount++;
        }


        for ($i = 0; $i < $phasesOther; $i++) {
            if (count($bonusdata) == 0) {
                break;
            }

            shuffle($bonusdata);
            $next = array_shift($bonusdata);

            $bphase[$bonusPhaseCount] = $this->createBonusPhase($next);
            $bonusPhaseCount++;
        }
        // end bonus

        $phases = [
            self::PHASE_COLONY => $phase, self::PHASE_ORBIT => $ophase, self::PHASE_UNDERGROUND => $uphase, self::PHASE_BONUS => $bphase
        ];

        [$colonyFields, $orbitFields, $undergroundFields] = $this->doPhases($config, $phases, $hasground);

        return $this->combine($colonyFields, $orbitFields, $undergroundFields);
    }

    private function doPhases(array $config, array $phases, bool $hasground): array
    {
        [$colonyFields, $orbitFields, $undergroundFields] = $this->initFields($config, $hasground);

        $colonyPhaseCounts = count($phases[self::PHASE_COLONY]);
        for ($i = 0; $i < $colonyPhaseCounts; $i++) {
            $colonyFields = $this->doPhase($phases[self::PHASE_COLONY][$i], $colonyFields);
        }

        $orbitPhaseCounts = count($phases[self::PHASE_ORBIT]);
        for ($i = 0; $i < $orbitPhaseCounts; $i++) {
            $orbitFields = $this->doPhase($phases[self::PHASE_ORBIT][$i], $orbitFields);
        }

        $undergroundPhaseCounts = count($phases[self::PHASE_UNDERGROUND]);
        for ($i = 0; $i < $undergroundPhaseCounts; $i++) {
            $undergroundFields = $this->doPhase($phases[self::PHASE_UNDERGROUND][$i], $undergroundFields);
        }

        $bonusPhaseCounts = count($phases[self::PHASE_BONUS]);
        for ($i = 0; $i < $bonusPhaseCounts; $i++) {
            $colonyFields = $this->doPhase($phases[self::PHASE_BONUS][$i], $colonyFields);
        }

        return [$colonyFields, $orbitFields, $undergroundFields];
    }

    private function initFields(array $config, bool $hasground): array
    {
        $h = $config[self::CONFIG_COLGEN_SIZEH];
        $w = $config[self::CONFIG_COLGEN_SIZEW];

        for ($i = 0; $i < $h; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $colfields[$j][$i] = $config[self::CONFIG_BASEFIELD_COLONY];
            }
        }
        $colfields[self::COLGEN_Y] = $h;
        $colfields[self::COLGEN_W] = $w;

        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < $w; $j++) {
                $orbfields[$j][$i] = $config[self::CONFIG_BASEFIELD_ORBIT];
            }
        }
        $orbfields[self::COLGEN_Y] = 2;
        $orbfields[self::COLGEN_W] = $w;

        $undergroundFields[self::COLGEN_Y] = 0;
        if ($hasground) {
            for ($i = 0; $i < 2; $i++) {
                for ($j = 0; $j < $w; $j++) {
                    $undergroundFields[$j][$i] = $config[self::CONFIG_BASEFIELD_UNDERGROUND];
                }
            }
            $undergroundFields[self::COLGEN_Y] = 2;
            $undergroundFields[self::COLGEN_W] = $w;
        }

        return [$colfields, $orbfields, $undergroundFields];
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

        return $requireResult;
    }

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
        $res[self::COLGEN_FROM] = [];
        $res[self::COLGEN_TO] = [];

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
        }

        return $res;
    }

    private function createBonusPhase($btype)
    {
        $bphase = [];

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

    private function getWeightingList(array $fields, $mode, array $from, $to, $adjacent, $no_adjacent, $noadjacentlimit = 0): ?array
    {
        $res = null;

        $width = count($fields);
        $height = count($fields[0] ?? []);
        $c = 0;
        for ($h = 0; $h < $height; $h++) {
            for ($w = 0; $w < $width; $w++) {

                //check if field is FROM
                if (!in_array($fields[$w][$h], $from)) {
                    continue;
                }

                //and now?
                $bw = 1;
                if ((($mode == "polar") || ($mode == "strict polar")) && ($h == 0 || $h == $height - 1)) {
                    $bw += 1;
                }
                if (($mode == GeneratorModeEnum::TOP_LEFT) && ($h == 0) && ($w == 0)) {
                    $bw += 2;
                }
                if (($mode == "polar seeding north") && ($h == 0)) {
                    $bw += 2;
                }
                if (($mode == "polar seeding south") && ($h == $height - 1)) {
                    $bw += 2;
                }

                if (($mode == "equatorial") && (($h == 2 && $height == 5) || (($h == 2 || $h == 3) && $height == 6))) {
                    $bw += 1;
                }

                if ($mode != "nocluster" && $mode != "forced adjacency" && $mode != "forced rim" && $mode != "polar seeding north" && $mode != "polar seeding south") {
                    for ($k = 0; $k < count($to); $k++) {
                        if ($fields[$w - 1][$h] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w + 1][$h] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w][$h - 1] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w][$h + 1] == $to[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w - 1][$h - 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w + 1][$h + 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w + 1][$h - 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w - 1][$h + 1] == $to[$k]) {
                            $bw += 0.5;
                        }
                    }
                }

                if ((($mode == "polar seeding north") && ($h == 0)) || (($mode == "polar seeding south") && ($h == $height - 1))) {
                    for ($k = 0; $k < count($to); $k++) {
                        if ($fields[$w - 1][$h] == $to[$k]) {
                            $bw += 2;
                        }
                        if ($fields[$w + 1][$h] == $to[$k]) {
                            $bw += 2;
                        }
                    }
                }

                if ($adjacent[0]) {
                    for ($k = 0; $k < count($adjacent); $k++) {
                        if ($fields[$w - 1][$h] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w + 1][$h] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w][$h - 1] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w][$h + 1] == $adjacent[$k]) {
                            $bw += 1;
                        }
                        if ($fields[$w - 1][$h - 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w + 1][$h + 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w + 1][$h - 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                        if ($fields[$w - 1][$h + 1] == $adjacent[$k]) {
                            $bw += 0.5;
                        }
                    }
                }

                if ($no_adjacent[0]) {
                    for ($k = 0; $k < count($no_adjacent); $k++) {
                        $ad = 0;
                        if ($fields[$w - 1][$h] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($fields[$w + 1][$h] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($fields[$w][$h - 1] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($fields[$w][$h + 1] == $no_adjacent[$k]) {
                            $ad += 1;
                        }
                        if ($fields[$w - 1][$h - 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($fields[$w + 1][$h + 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($fields[$w + 1][$h - 1] == $no_adjacent[$k]) {
                            $ad += 0.5;
                        }
                        if ($fields[$w - 1][$h + 1] == $no_adjacent[$k]) {
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

                if (($mode == "polar") && ($h > 1) && ($h < $height - 2)) {
                    $bw = 0;
                }
                if (($mode == "strict polar") && ($h > 0) && ($h < $height - 1)) {
                    $bw = 0;
                }
                if ($mode == "polar seeding north" && ($h > 1)) {
                    $bw = 0;
                }
                if ($mode == "polar seeding south" && ($h < $height - 2)) {
                    $bw = 0;
                }
                if (($mode == "equatorial") && (($h < 2) || ($h > 3)) && ($height == 6)) {
                    $bw = 0;
                }
                if (($mode == "equatorial") && (($h < 2) || ($h > 3)) && ($height == 5)) {
                    $bw = 0;
                }

                if (($mode == "lower orbit") && ($h != 1)) {
                    $bw = 0;
                }
                if (($mode == "upper orbit") && ($h != 0)) {
                    $bw = 0;
                }

                if (($mode == "tidal seeding") && ($w != 0)) {
                    $bw = 0;
                }

                if (($mode == GeneratorModeEnum::TOP_LEFT) && (($h != 0) || $w != 0)) {
                    $bw = 0;
                }
                if (($mode == "right") && ($fields[$w - 1][$h] != $adjacent[0])) {
                    $bw = 0;
                }
                if (($mode == "below") && ($fields[$w][$h - 1] != $adjacent[0])) {
                    $bw = 0;
                }
                if (($mode == "crater seeding") && (($w == $width - 1) || ($h == $height - 1))) {
                    $bw = 0;
                }

                if ($bw > 0) {
                    $res[$c][self::COLGEN_X] = $w;
                    $res[$c][self::COLGEN_Y] = $h;
                    $res[$c][self::COLGEN_BASEWEIGHT] = $bw;
                    $c++;
                }
            }
        }
        return $res;
    }

    private function doPhase(array $phase, $fields)
    {
        if ($phase[self::COLGEN_MODE] == "fullsurface") {
            $k = 0;
            for ($ih = 0; $ih < $fields[self::COLGEN_Y]; $ih++) {
                for ($iw = 0; $iw < $fields[self::COLGEN_W]; $iw++) {

                    $k++;

                    $fields[$iw][$ih] = $phase[self::COLGEN_TYPE] * 100 + $k;
                }
            }
        } else {
            for ($i = 0; $i < $phase[self::COLGEN_NUM]; $i++) {
                $arr = $this->getWeightingList(
                    $fields,
                    $phase[self::COLGEN_MODE],
                    $phase[self::COLGEN_FROM],
                    $phase[self::COLGEN_TO],
                    $phase[self::COLGEN_ADJACENT],
                    $phase[self::COLGEN_NOADJACENT],
                    $phase[self::COLGEN_NOADJACENTLIMIT]
                );
                if ($arr === null || count($arr) == 0) {
                    break;
                }

                $field = $this->weightedDraw($arr, $phase[self::COLGEN_FRAGMENTATION]);
                $ftype = $fields[$field[self::COLGEN_X]][$field[self::COLGEN_Y]];

                $t = 0;
                unset($ta);
                for ($c = 0; $c < count($phase[self::COLGEN_FROM]); $c++) {

                    if ($ftype == $phase[self::COLGEN_FROM][$c]) {
                        $ta[$t] = $phase[self::COLGEN_TO][$c];
                        $t++;
                    }
                }
                if ($t > 0) {
                    $fields[$field[self::COLGEN_X]][$field[self::COLGEN_Y]] = $ta[rand(0, $t - 1)];
                }
            }
        }
        return $fields;
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
}
