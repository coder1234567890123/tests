<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountsApiTest extends WebTestCase
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

        echo "\nAccountsApiTest:  testing Auth \n";

        $this->Response401('GET', '/api/accounts/usage');
        $this->Response401('GET', '/api/accounts/usage-export');
        $this->Response401('GET', '/api/accounts/monthly-reset/1');

    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".AccountsApiTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}