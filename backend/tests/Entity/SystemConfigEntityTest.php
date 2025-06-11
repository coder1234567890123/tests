<?php

namespace App\Tests\Util;

use App\Entity\SystemConfig;
use PHPUnit\Framework\TestCase;

class SystemConfigEntityTest extends TestCase
{
    public function testSystemConfigGetterAndSetter()
    {
        echo "\nSystemConfigEntityTest:  SystemConfig Entity\n";

        $systemConfig = new SystemConfig();

        $systemConfig->setOpt('option');
        $this->assertEquals('option', $systemConfig->getOpt());

        $systemConfig->setVal('value');
        $this->assertEquals('value', $systemConfig->getVal());

        $systemConfig->setSystemType('1');
        $this->assertEquals('1', $systemConfig->getSystemType());

    }
}
