<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class SecurityApiTest extends WebTestCase
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

        $testName = "SecurityApiTest";

        echo "\n $testName:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => $testName,
            "url" => "/api/login",
            "method" => "POST",
            "test_type" => "Response500"
        ];

        $testService->startTests($testInfo);

//        $testInfo = [
//            "name" => $testName,
//            "url" => "/api/reset-password",
//            "method" => "POST",
//            "test_type" => "Response401"
//        ];
//
//        $testService->startTests($testInfo);

//        $testInfo = [
//            "name" => $testName,
//            "url" => "/api/reset-password/123",
//            "method" => "POST",
//            "test_type" => "Response401"
//        ];
//
//        $testService->startTests($testInfo);
    }

}