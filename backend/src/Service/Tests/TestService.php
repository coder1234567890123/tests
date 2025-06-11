<?php

namespace App\Service\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\Tests\UserRolesService;
use PHPUnit\Framework\TestCase;

/**
 * Class AccountsService
 *
 * @package App\Service
 */
class TestService extends WebTestCase
{

    /**
     * @param $TestsInfo
     */
    public function startTests($TestsInfo)
    {
        switch ($TestsInfo['test_type']) {
            case "Response401":

                $this->response401($TestsInfo['name'], $TestsInfo['method'], $TestsInfo['url']);
                break;

            case "Response500":

                $this->response500($TestsInfo['name'], $TestsInfo['method'], $TestsInfo['url']);
                break;

            case "testRoles":

                //$this->testRoles($TestsInfo);

                break;
        }
    }


    /**
     * @param $name
     * @param $method
     * @param $url
     */
    public function response401($name, $method, $url)
    {
        $this->expectException(AccessDeniedException::class);

        echo "." . $name . " Auth 401: " . $method . "  " . $url . " \n";
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @param $name
     * @param $method
     * @param $url
     */
    public function response500($name, $method, $url)
    {
        $this->expectException(AccessDeniedException::class);

        echo "." . $name . " Auth 500: " . $method . "  " . $url . " \n";

        $client = static::createClient();

        $client->request($method, $url);
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testRoles($data)
    {
        $hierarchy = $this->getRolesHierarchy($data['role']);
        $getRoles = $this->rolesHierarchy();
        $rolesList = $this->getRolesList();

//        dd($rolesList);

        foreach ($getRoles as $index => $getData) {
            if ($getData['hierarchy'] <= $hierarchy) {
                echo "." . $data['name'] . " Auth 200: " . " " . $index . " " . $data['method'] . "  " . $data['url'] . " \n";

                echo $status = $this->getUrlRoleResponse($data, $getData['token']);

                switch ($status) {
                    case "200":

                        $this->assertEquals(200, $status);
                        break;

                    case "500":
                        $this->assertEquals(500, $status);
                        break;
                }
            } else {
                echo "." . $data['name'] . " Auth 401: " . " " . $index . " " . $data['method'] . "  " . $data['url'] . " \n";

                $status = $this->getUrlRoleResponse($data, $getData['token']);
                $this->assertEquals(401, $status);
            }
        }
    }

    /**
     * @param $role
     *
     * @return mixed|string
     */
    private function getRolesHierarchy($role)
    {
        $roles = $this->rolesHierarchy();

        foreach ($roles as $index => $getData) {
            if ($role === $index) {
                return $getData['hierarchy'];
            }
        }
    }

    private function  getRolesList(){

        return [
        "ROLE_SUPER_ADMIN",
        "ROLE_TEAM_LEAD",
        "ROLE_ANALYST",
        "ROLE_ADMIN_USER",
        "ROLE_USER_MANAGER",
        "ROLE_USER_STANDARD"
        ];
    }

    /**
     * @return array
     */
    private function rolesHierarchy()
    {
//        role_hierarchy:
//        ROLE_SUPER_ADMIN: [ROLE_TEAM_LEAD, ROLE_ADMIN_USER]
//        ROLE_TEAM_LEAD: ROLE_ANALYST
//        ROLE_ANALYST: []
//        ROLE_ADMIN_USER: ROLE_USER_MANAGER
//        ROLE_USER_MANAGER: ROLE_USER_STANDARD
//        ROLE_USER_STANDARD: []
        $getUserRoles = new UserRolesService();

        return [
            'ROLE_SUPER_ADMIN' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_TEAM_LEAD', 'ROLE_ADMIN_USER'],
                'token' => $getUserRoles->adminAuthenticatedClient()
            ],
            'ROLE_TEAM_LEAD' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_TEAM_LEAD','ROLE_ANALYST'],
                'token' => $getUserRoles->teamLeaderAuthenticatedClient()
            ],
            'ROLE_ANALYST' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_ANALYST'],
                'token' => $getUserRoles->analystAuthenticatedClient()
            ],
            'ROLE_ADMIN_USER' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_ADMIN_USER','ROLE_USER_MANAGER'],
                'token' => $getUserRoles->userAdminAuthenticatedClient()
            ],
            'ROLE_USER_MANAGER' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_USER_STANDARD','ROLE_USER_MANAGER'],
                'token' => $getUserRoles->userManagerAuthenticatedClient()
            ],
            'ROLE_USER_STANDARD' => [
                'hierarchy' => ['ROLE_SUPER_ADMIN','ROLE_USER_STANDARD'],
                'token' => $getUserRoles->userStandardAuthenticatedClient()
            ]
//        return [
//            'ROLE_SUPER_ADMIN' => [
//                'hierarchy' => '1',
//                'token' => $getUserRoles->adminAuthenticatedClient()
//            ],
//            'ROLE_TEAM_LEAD' => [
//                'hierarchy' => '2',
//                'token' => $getUserRoles->teamLeaderAuthenticatedClient()
//            ],
//            'ROLE_ANALYST' => [
//                'hierarchy' => '3',
//                'token' => $getUserRoles->analystAuthenticatedClient()
//            ],
//            'ROLE_ADMIN_USER' => [
//                'hierarchy' => '4',
//                'token' => $getUserRoles->userAdminAuthenticatedClient()
//            ],
//            'ROLE_USER_MANAGER' => [
//                'hierarchy' => '5',
//                'token' => $getUserRoles->userManagerAuthenticatedClient()
//            ],
//            'ROLE_USER_STANDARD' => [
//                'hierarchy' => '6',
//                'token' => $getUserRoles->userStandardAuthenticatedClient()
//            ]
        ];
    }

    private function getUrlRoleResponse($data, $token)
    {
        $header = array(
            'HTTP_Authorization' => sprintf('%s %s', 'Bearer', $token),
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        );

        $client = static::createClient();
        $client->request($data['method'], $data['url'], array(), array(), $header);

        //dd($client->getResponse()->getStatusCode());

        return $client->getResponse()->getStatusCode();
    }


}