<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\CountryRepository;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class CountriesController
 *
 * @package App\Controller
 */
class CountriesController
{
    /**
     * @param CountryRepository   $repository
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @Route("/api/country", methods={"GET"}, name="country_get")
     * @Security("is_granted('ROLE_ANALYST') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the countries",
     * )
     * @SWG\Tag(name="country")
     *
     * @Areas({"internal","default"})
     */
    public function getAction(CountryRepository $repository, SerializerInterface $serializer)
    {
        $countries = $repository->all();

        return new Response(
            $serializer->serialize($countries, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }
}