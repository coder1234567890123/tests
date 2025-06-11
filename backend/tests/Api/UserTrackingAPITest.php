<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class UserTrackingAPITest extends WebTestCase
{


    public function setUp()
    {

    }

    public function tearDown(): void
    {

        Mockery::close();
    }

    /**
     * @test
     */
    public function AuthTest()
    {

        $testName = "UserTrackingAPITest";

        echo "\n $testName:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => $testName,
            "url" => "/api/usertracking",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/usertracking/export",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}