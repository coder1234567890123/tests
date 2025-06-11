<?php

namespace App\Tests\Util;

use App\Entity\Address;
use App\Entity\DefaultBranding;
use PHPUnit\Framework\TestCase;

class DefaultBrandingEntityTest extends TestCase
{
    public function testPhaseGetterAndSetter()
    {
        echo "\nDefaultBrandingEntityTest:  Default Branding Entity \n";

        $defaultBranding = new DefaultBranding();

        $defaultBranding->setThemeColor('#166c36');
        $this->assertEquals('#166c36', $defaultBranding->getThemeColor());

        $defaultBranding->setFooterLink('url');
        $this->assertEquals('url', $defaultBranding->getFooterLink());

        $defaultBranding->setDisclaimer('this is a message');
        $this->assertEquals('this is a message', $defaultBranding->getDisclaimer());

        $defaultBranding->setFrontPage('url');
        $this->assertEquals('url', $defaultBranding->getFrontPage());

        $defaultBranding->setCoFrontPage('url');
        $this->assertEquals('url', $defaultBranding->getCoFrontPage());

        $defaultBranding->setLogo('url');
        $this->assertEquals('url', $defaultBranding->getLogo());
    }
}
