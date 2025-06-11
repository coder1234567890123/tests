<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultBrandingTest extends WebTestCase
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

        echo "\nDefaultBrandingTest:  testing Auth \n";

        $this->Response401('GET', '/api/default-branding');
        $this->Response401('PATCH', '/api/default-branding/1');
        $this->Response401('POST', '/api/default-branding/images');

    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".DefaultBrandingTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}