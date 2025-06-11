<?php

use PHPUnit\Framework\TestCase;
use App\Service\ApiReturnService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentApiTest extends WebTestCase
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

        echo "\nCommentApiTest:  testing Auth \n";

        $this->Response401('POST', '/api/comment');
        $this->Response401('GET', '/api/comment/1');
        $this->Response401('GET', '/api/accounts/monthly-reset/1');
        $this->Response401('PATCH', '/api/comment/1');
        $this->Response401('DELETE', '/api/comment/1');
        $this->Response401('GET', '/api/comment/subject/1');
        $this->Response401('PUT', '/api/comment/1/enable');
        $this->Response401('PUT', '/api/comment/1/enable');
        $this->Response401('PATCH', '/api/comment/1/hide');
        $this->Response401('PATCH', '/api/comment/1/show');

    }


    /**
     * @param $method
     * @param $url
     */
    private function Response401($method, $url)
    {

        echo ".CommentApiTest Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }


}