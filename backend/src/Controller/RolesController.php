<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\RoleGroupRepository;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\RoleGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class RolesController
 *
 * @package App\Controller
 */
class RolesController extends AbstractController
{
    /**
     * @param RoleGroupRepository $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/roles", name="role_get", methods={"GET"})
     * @IsGranted("ROLE_USER_MANAGER")
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all roles.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=RoleGroup::class))
     *     )
     * )
     * @SWG\Tag(name="role")
     *
     *  @Areas({"internal"})
     */
    public function indexAction(RoleGroupRepository $repository, SerializerInterface $serializer)
    {
        $roles = $repository->all();

        return new Response(
            $serializer->serialize($roles, 'json'),
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }
}