<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use App\Service\Tests\TestService;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class UserTrackingAPITest
 */
class ProductApiTest extends WebTestCase
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

        $testName = "ProductApiTest";

        echo "\n $testName:  testing Auth \n";

        $testService = new TestService();

        $testInfo = [
            "name" => $testName,
            "url" => "/api/product",
            "method" => "GET",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/product",
            "method" => "POST",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/product/1",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/product/1/enable",
            "method" => "PATCH",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

        $testInfo = [
            "name" => $testName,
            "url" => "/api/product/1/disable",
            "method" => "PUT",
            "test_type" => "Response401"
        ];

        $testService->startTests($testInfo);

    }

}