<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportingApiTest extends WebTestCase
{


    public function setUp()
    {
        //$this->ApiReturnService = $this->createMock(ApiReturnService::class);
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

        echo "\nReportingApiTest:  testing Auth \n";

        $this->Response401('GET', '/api/reporting/dashboard');
        $this->Response401('GET', '/api/reporting/1/company-monthly');
        $this->Response401('GET', '/api/reporting/1/company-daterange');
        $this->Response401('GET', '/api/reporting/1/user-daterange');
        $this->Response401('GET', '/api/reporting/1/exportUserDateRange');
        $this->Response401('GET', '/api/reporting/1/exportUserMonthly');

    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".ReportingApiTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}