<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 7/23/19
 * Time: 8:14 AM
 */

use App\Entity\GlobalWeights;
use App\Entity\Team;
use App\Entity\Company;
use PHPUnit\Framework\TestCase;
use App\Entity\User;

class GlobalWeightsEntityTest extends TestCase
{
    public function testGlobalWeightsGetterAndSetter()
    {

        echo "\nGlobalWeightsEntityTest:  Global Weights Entity \n";

        $globalWeights   = new GlobalWeights();

        $globalWeights->setSocialPlatform('facebook');
        $this->assertEquals($globalWeights->getSocialPlatform(), 'facebook');

        $globalWeights->setPrePlatformScoringMetric(25);
        $this->assertEquals($globalWeights->getPrePlatformScoringMetric(), 25);

        $globalWeights->setPostPlatformScoringMetric(25);
        $this->assertEquals($globalWeights->getPrePlatformScoringMetric(), 25);

        $globalWeights->setGlobalUsageWeighting(25);
        $this->assertEquals($globalWeights->getGlobalUsageWeighting(), 25);

        $globalWeights->setVersion(1);
        $this->assertEquals($globalWeights->getVersion(), 1);

        $globalWeights->setOrdering(1);
        $this->assertEquals($globalWeights->getOrdering(), 1);

    }
}