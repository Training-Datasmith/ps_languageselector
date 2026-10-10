<?php

abstract class ModuleTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        FixtureReset::resetAll();
    }
}
