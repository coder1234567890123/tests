<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class QualificationApiTest extends WebTestCase
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

        $testName = "QualificationApiTest";

        echo "\n $testName:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => $testName,
            "url" => "/api/qualification",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/qualification",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/qualification/1",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/subject/1/qualification/1",
            "method" => "DELETE",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}