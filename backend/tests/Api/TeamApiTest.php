<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class TeamApiTest
 */
class TeamApiTest extends WebTestCase
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

        echo "\nUserTrackingAPITest:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/paginated",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/teamLead",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/subject/1",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/1",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/1",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/1",
            "method" => "DELETE",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => "TeamApiTest",
            "url" => "/api/team/1/assign",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}