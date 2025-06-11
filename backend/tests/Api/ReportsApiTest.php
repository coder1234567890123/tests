<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportsApiTest extends WebTestCase
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

        echo "\nReportsApiTest:  testing Auth \n";

        $this->Response401('GET', '/api/report');
        $this->Response401('GET', '/api/report/1');
        $this->Response401('PATCH', '/api/report/1');
        $this->Response401('DELETE', '/api/report/1');
        $this->Response401('GET', '/api/report/1/information');
        $this->Response401('GET', '/api/report/1/duplicate/1');
        $this->Response401('GET', '/api/report/1/duplicate_search/1');
        $this->Response401('PUT', '/api/report/1/enable');
        $this->Response401('PUT', '/api/report/1/close');
        $this->Response401('PUT', '/api/report/open');
        $this->Response401('GET', '/api/report/subject/reports');
        $this->Response401('GET', '/api/report/1/status');
        $this->Response401('POST', '/api/report/subject/1/request');
        $this->Response401('POST', '/api/report/subject/1/new_invest');
        $this->Response401('POST', '/api/report/queue/1/approve');
        $this->Response401('GET', '/api/report/subject/1/status');
        $this->Response401('GET', '/api/report/subject/1');
        $this->Response401('GET', '/api/report/subject/1/pdf-standard');
        $this->Response401('GET', '/api/report/subject/1/pdf-rebuild');
        $this->Response401('GET', '/api/report/subject/1/pdf');
        $this->Response401('PATCH', '/api/report/1/toggleGeneralComments');
        $this->Response401('PATCH', '/api/report/1/toggleReportScore');
        $this->Response401('GET', '/api/report/1/risk-comments');
        $this->Response401('GET', '/api/report/subject/1/web');
        $this->Response401('GET', '/api/report/subject/1/get-scores');
        $this->Response401('GET', '/api/report/subject/1/get-edit-report');
        $this->Response401('PATCH', '/api/report/subject/1/update-edit-report-scores');
        $this->Response401('GET', '/api/report/subject/1/build-math');
        $this->Response401('GET', '/api/reporting/export');
        $this->Response401('GET', '/api/report/1/abandoned');
        $this->Response401('GET', '/api/report/1/cancel-abandoned');


    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".ReportsApiTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}