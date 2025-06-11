<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Service\ApiErrorsService;
use App\Service\Validator;
use App\Service\ApiReturnService;
use Exception;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Employment;
use App\Repository\EmploymentRepository;
use App\Repository\CountryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class EmploymentController
 *
 * @package App\Controller
 */
class EmploymentController
{
    /**
     * @param EmploymentRepository $repository
     * @param SerializerInterface  $serializer
     *
     * @return Response
     *
     * @Route("/api/employment", methods={"GET"}, name="employment_get")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the employments",
     * )
     * @SWG\Tag(name="employment")
     *
     * @Areas({"internal"})
     */
    public function getAction(EmploymentRepository $repository, SerializerInterface $serializer)
    {
        $employment = $repository->all();

        return new Response(
            $serializer->serialize($employment, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param SerializerInterface $serializer
     * @param Employment          $employment
     *
     * @return Response
     *
     * @Route("/api/employment/{id}", methods={"GET"}, name="employment_get_id")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the employments for subject",
     * )
     * @SWG\Tag(name="employment")
     *
     * @Areas({"internal"})
     */
    public function getIDAction(
        SerializerInterface $serializer,
        Employment $employment
    )
    {
        return new Response(
            $serializer->serialize(
                $employment,
                'json',
                SerializationContext::create()->setGroups(["read"])
            ),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param EmploymentRepository $repository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param Request              $request
     * @param Subject              $subject
     * @param ApiReturnService     $apiReturnService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/employment", methods={"POST"}, name="employment_post")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Create a qualification object, for Subject",
     * )
     *
     *
     * @SWG\Tag(name="employment")
     *
     * @Areas({"internal","default"})
     */
    public function postAction(
        EmploymentRepository $repository,
        CountryRepository $countryRepository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        // Remove Invalid Properties
        $data = json_decode($request->getContent(), true);
        unset($data['id']);

        /** @var Employment $employment */
        $employment = $serializer->deserialize(
            json_encode($data),
            Employment::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        try{
            if($employment->getCountry()==null) {
                // Getting country in format of string: vuetify-country-region-select
                $countryJsonArr = json_decode($request->getContent(),true);
                $country = $data['country'];
                //return $apiErrorsService->errorFourHundred($country . "/r/n" . serialize($data));
                $employment->setCountry($countryRepository->byName($country));
            }
        }
        catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }

        /** @var JsonResponse $response */
        if (($response = $validator->validate($employment)) !== false) {
            return $response;
        }

        // Associate Subject
        $employment->setSubject($subject);

        // Valid Entity
        try {
            $addedEmployment = $repository->save($employment, $subject);

            return new Response(
                $serializer->serialize(
                    $apiReturnService->getSubject($subject),
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param EmploymentRepository $repository
     * @param SerializerInterface  $serializer
     * @param Validator            $validator
     * @param Request              $request
     * @param Subject              $subject
     * @param Employment           $employment
     * @param ApiReturnService     $apiReturnService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{subject}/employment/{employment}", methods={"PATCH"}, name="employment_update")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("employment", class="App\Entity\Employment", options={"id" = "employment"})
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update a qualification object, for Subject",
     * )
     *
     *
     * @SWG\Tag(name="employment")
     *
     * @Areas({"internal","default"})
     */
    public function updateAction(
        EmploymentRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        Employment $employment,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $employment->getId();

        /** @var Employment $employment */
        $employment = $serializer->deserialize(
            json_encode($data),
            Employment::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($employment)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($employment, $subject);

            return new Response(
                $serializer->serialize(
                    $apiReturnService->getSubject($subject),
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }

    /**
     * @param EmploymentRepository $repository
     * @param SerializerInterface  $serializer
     * @param Subject              $subject
     * @param Employment           $employment
     * @param ApiReturnService     $apiReturnService
     *
     * @param ApiErrorsService     $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{subject}/employment/{employment}", methods={"delete"}, name="employment_delete")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("employment", class="App\Entity\Employment", options={"id" = "employment"})
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Delete to Employment.",
     *    )
     *
     * @SWG\Tag(name="employment")
     *
     * @Areas({"internal","default"})
     */
    public function deleteAction(
        EmploymentRepository $repository,
        SerializerInterface $serializer,
        Subject $subject,
        Employment $employment,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        // Valid Entity
        try {
            $repository->delete($employment);

            return new Response(
                $serializer->serialize(
                    $apiReturnService->getSubject($subject),
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            return $apiErrorsService->errorFiveHundred($e);
        }
    }
}