<?php

namespace App\Service\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\TestCase;

/**
 * Class AccountsService
 *
 * @package App\Service
 */
class UserRolesService extends WebTestCase
{


    /**
     *
     */
    public function adminAuthenticatedClient()
    {
        $userData = [
            'email' => 'admin@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }


    /**
     *
     */
    public function teamLeaderAuthenticatedClient()
    {
        $userData = [
            'email' => 'teamlead@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }

    /**
     *
     */
    public function analystAuthenticatedClient()
    {
        $userData = [
            'email' => 'analyst@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }

    /**
     *
     */
    public function userAdminAuthenticatedClient()
    {
        $userData = [
            'email' => 'adminuser@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }

    /**
     *
     */
    public function userManagerAuthenticatedClient()
    {
        $userData = [
            'email' => 'manageruser@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }

    /**
     *
     */
    public function userStandardAuthenticatedClient()
    {
        $userData = [
            'email' => 'standarduser@testing.test',
            'password' => 'Password123'
        ];

        return $this->loginGetToken($userData);
    }

    public function loginGetToken($userData)
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'email' => $userData['email'],
                'password' => $userData['password'],
            ))
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'];
    }

}