<?php

namespace Stu\Control;

use AccessViolation;
use request;
use ResearchDependency;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Tuple;

final class ResearchController extends GameController
{

    private $default_tpl = "html/research.xhtml";

    private $researchRepository;

    private $researchedRepository;

    public function __construct(
        SessionInterface $session,
        ResearchRepositoryInterface $researchRepository,
        ResearchedRepositoryInterface $researchedRepository
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Forschung");
        $this->addNavigationPart(new Tuple("research.php", "Forschung"));

        $this->addCallback('B_DO_RESEARCH', 'doResearch', true);
        $this->addCallback('B_CANCEL_CURRENT_RESEARCH', 'cancelResearch', true);

        $this->addView("SHOW_RESEARCH", "showResearch");
        $this->researchRepository = $researchRepository;
        $this->researchedRepository = $researchedRepository;
    }

    protected function doResearch()
    {
        if (currentUser()->getCurrentResearch()) {
            $this->researchedRepository->delete(currentUser()->getCurrentResearch());
        }
        if (!array_key_exists($this->getSelectedResearch()->getId(), $this->getResearchList())) {
            new AccessViolation;
        }

        $research = $this->researchedRepository->prototype();
        $research->setActive($this->getSelectedResearch()->getPoints());
        $research->setUserId(currentUser()->getId());
        $research->setResearchId($this->getSelectedResearch()->getId());
        $research->setFinished(0);

        $this->researchedRepository->save($research);

        currentUser()->currentResearch = null;
        $this->addInformation($this->getSelectedResearch()->getName() . " wird erforscht");
    }

    protected function cancelResearch()
    {
        if (currentUser()->getCurrentResearch()) {
            $this->researchedRepository->delete(currentUser()->getCurrentResearch());
        }
        currentUser()->currentResearch = null;
        $this->addInformation("Die laufende Forschung wurde abgebrochen");
    }

    protected function showResearch()
    {
        $this->setPageTitle("Forschung: " . $this->getSelectedResearch()->getName());
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/researchmacros.xhtml/details');
    }

    public function getSelectedResearch()
    {
        return $this->researchRepository->find(request::getIntFatal('id'));
    }

    private $researchList = null;

    public function getResearchList()
    {
        if ($this->researchList === null) {

            $finished_list = array_map(
                function (ResearchedInterface $researched): int {
                    return $researched->getResearchId();
                },
                $this->getFinishedResearchList()
            );

            $result = $this->researchRepository->getAvailableResearch((int) currentUser()->getId());
            $dependencies = ResearchDependency::getList();
            $excludes = ResearchDependency::getListExcludes();
            foreach ($result as $obj) {
                $key = $obj->getId();
                if (isset($excludes[$key])) {
                    foreach ($excludes[$key] as $exclude) {
                        if (in_array($exclude->getResearchId(), $finished_list)) {
                            continue 2;
                        }
                    }
                }
                if (!isset($dependencies[$key])) {
                    $this->researchList[$key] = $obj;
                    continue;
                }
                $grouped_list = array();
                foreach ($dependencies[$key] as $dependency) {
                    if (!isset($grouped_list[$dependency->getMode()])) {
                        $grouped_list[$dependency->getMode()] = array();
                    }
                    if ($dependency->getMode() != RESEARCH_MODE_EXCLUDE) {
                        $grouped_list[$dependency->getMode()][] = $dependency;
                    }
                }
                if (count($grouped_list) > 0) {
                    foreach ($grouped_list as $group) {
                        $found = false;
                        foreach ($group as $dependency) {
                            if (in_array($dependency->getDependOn(), $finished_list)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            continue 2;
                        }
                    }
                }
                $this->researchList[$key] = $obj;
            }
            foreach (getDefaultTechs() as $research_id) {
                if (isset($this->researchList[$research_id])) {
                    unset($this->researchList[$research_id]);
                }
            }
        }
        return $this->researchList;
    }

    private $finishedResearchList = null;

    public function getFinishedResearchList()
    {
        if ($this->finishedResearchList === null) {
            $this->finishedResearchList = $this->researchedRepository->getListByUser((int) currentUser()->getId());
            usort(
                $this->finishedResearchList,
                function (ResearchedInterface $a, ResearchedInterface $b): int {
                    return $a->getFinished() <=> $b->getFinished();
                }
            );
        }
        return $this->finishedResearchList;
    }
}
