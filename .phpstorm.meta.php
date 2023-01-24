<?php
namespace PHPSTORM_META
{
    override(\Psr\Container\ContainerInterface::get(0), map([
        '' => '@',
    ]));
    override(\DI\Container::get(0), map([
        '' => '@',
    ]));
    override(\Mockery::mock(0), type(0));
    override(\Stu\StuTestCase::mock(0), type(0));
}
