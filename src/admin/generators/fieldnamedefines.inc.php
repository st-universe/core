<?php

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Lib\DefaultGenerator;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\PlanetFieldTypeInterface;

include_once(__DIR__ . '/../../inc/config.inc.php');

class FieldNameDefineGenerator extends DefaultGenerator
{
    /**
     * @var ObjectRepository
     */
    private $planetTypeRepository;

    function __construct(
        ObjectRepository $planetTypeRepository
    ) {
        $this->planetTypeRepository = $planetTypeRepository;
        parent::__construct();
        $this->writeSuffix();
    }

    protected $file = 'fieldtypesname.inc.php';

    protected function handle()
    {
        $this->write('function getFieldName($value) {');
        $this->write('switch ($value) {');

        /**
         * @var PlanetFieldTypeInterface[] $result
         */
        $result = $this->planetTypeRepository->findAll();
        foreach ($result as $type) {
            $this->write("case " . $type->getFieldType() . ":");
            $this->write("return _('" . $type->getDescription() . "');");
        }

        $this->write("}");
        $this->write("}");
    }
}

new FieldNameDefineGenerator(
    $container->get(EntityManagerInterface::class)->getRepository(PlanetFieldType::class)
);
