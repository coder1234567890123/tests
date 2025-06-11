<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class SystemConfigApiTest extends WebTestCase
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

        echo "\nSystemConfigApiTest:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => "SystemConfigApiTest",
            "url" => "/api/systemconfig",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testService = new TestService();

        $testInfo = [
            "name" => "SystemConfigApiTest",
            "url" => "/api/systemconfig/1",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "SystemConfigApiTest",
            "url" => "/api/systemconfig/systemassets/1",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "SystemConfigApiTest",
            "url" => "/api/systemconfig/systemassets/list",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}