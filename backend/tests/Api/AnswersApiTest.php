<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnswersApiTest extends WebTestCase
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

        echo "\nAnswersApiTest:  testing Auth \n";

        $this->Response401('GET', '/api/answer');
        $this->Response401('POST', '/api/answer');
        $this->Response401('GET', '/api/answer/1');
        $this->Response401('PATCH', '/api/answer/1');
        $this->Response401('DELETE', '/api/answer/1');
        $this->Response401('PUT', '/api/answer/1/enable');
        $this->Response401('GET', '/api/answer/question/1/skip');
        $this->Response401('GET', '/api/answer/question/1/applicable');

    }

    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".AnswersApiTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }
}