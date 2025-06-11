<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class SubjectApiTest extends WebTestCase
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

        $testName = "SubjectApiTest";

        echo "\n$testName:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1",
            "method" => "DELETE",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/refresh",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/enable",
            "method" => "PUT",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/image",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/image",
            "method" => "DELETE",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/image/1/list",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/message_bus/1",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/message_bus/overwrite/1",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}