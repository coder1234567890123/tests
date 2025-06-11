<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserAPITest extends WebTestCase
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

        echo "\nUserAPITest:  testing Auth \n";

        $this->Response401('GET','/api/companies/current-company');
        $this->Response401('GET','/api/users/me');
        $this->Response401('PATCH','/api/users/reset-company/1');
        $this->Response401('PATCH','/api/users/update');
        $this->Response401('GET','/api/users');
        $this->Response401('POST','/api/users');
        $this->Response401('GET','/api/users/1');
        $this->Response401('PATCH','/api/users/1');
        $this->Response401('PUT','/api/user/1/enable');
        $this->Response401('DELETE','/api/user/1');
        $this->Response401('PUT','/api/user/1/archive');
        $this->Response401('POST','/api/user/1/image');
        $this->Response401('DELETE','/api/user/1/image');
        $this->Response401('GET','/api/user/image/1/list');
        $this->Response401('DELETE','/api/user/team-remove/1');
    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method,$url){

        echo ".UserAPITest Auth 401: ".$method."  ". $url." \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}