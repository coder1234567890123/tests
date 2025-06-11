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
use App\Entity\Qualification;
use App\Repository\QualificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class qualificationController
 *
 * @package App\Controller
 */
class QualificationController
{
    /**
     * @param QualificationRepository $repository
     * @param SerializerInterface     $serializer
     *
     * @return Response
     *
     * @Route("/api/qualification", methods={"GET"}, name="qualification_get")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a list of all the qualifications",
     * )
     *
     * @SWG\Tag(name="qualification")
     *
     * @Areas({"internal"})
     */
    public function getAction(QualificationRepository $repository, SerializerInterface $serializer)
    {
        $qualification = $repository->all();

        return new Response(
            $serializer->serialize($qualification, 'json'),
            200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param QualificationRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param Subject                 $subject
     * @param ApiReturnService        $apiReturnService
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{id}/qualification", methods={"POST"}, name="qualification_post")
     * @ParamConverter("subject", class="App\Entity\Subject")
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     * @SWG\Response(
     *     response="200",
     *     description="Create a qualification object.",
     * )
     *
     *
     * @SWG\Tag(name="qualification")
     *
     * @Areas({"internal","default"})
     */
    public function postAction(
        QualificationRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);

        $data = $data['qualification'];

        /** @var Qualification $qualification */
        $qualification = $serializer->deserialize(
            json_encode($data),
            Qualification::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        $qualification->setSubject($subject);

        /** @var JsonResponse $response */
        if (($response = $validator->validate($qualification)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
           $repository->save($qualification);

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
     * @param QualificationRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param Subject                 $subject
     * @param Qualification           $qualification
     * @param ApiReturnService        $apiReturnService
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{subject}/qualification/{qualification}", methods={"PATCH"}, name="qualification_update")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("qualification", class="App\Entity\Qualification", options={"id" = "qualification"})
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the company entity.",

     * )
     * @SWG\Tag(name="qualification")
     *
     * @Areas({"internal","default"})
     */
    public function updateAction(
        QualificationRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        Qualification $qualification,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data = $data['qualification'];

        $data['id'] = $qualification->getId();

        /** @var Qualification $qualification */
        $qualification = $serializer->deserialize(
            json_encode($data),
            Qualification::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($qualification)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($qualification);

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
     * @param QualificationRepository $repository
     * @param SerializerInterface     $serializer
     * @param Validator               $validator
     * @param Request                 $request
     * @param Subject                 $subject
     * @param Qualification           $qualification
     * @param ApiReturnService        $apiReturnService
     *
     * @param ApiErrorsService        $apiErrorsService
     *
     * @return Response
     *
     * @Route("/api/subject/{subject}/qualification/{qualification}", methods={"DELETE"}, name="qualification_delete")
     * @ParamConverter("subject", class="App\Entity\Subject", options={"id" = "subject"})
     * @ParamConverter("qualification", class="App\Entity\Qualification", options={"id" = "qualification"})
     * @Security("is_granted('ROLE_TEAM_LEAD') or is_granted('ROLE_USER_STANDARD', subject)")
     *
     * @SWG\Response(
     *     response="200",
     *     description="update to Qualification."
     * )
     * @SWG\Tag(name="qualification")
     *
     * @Areas({"internal","default"})
     */
    public function deleteAction(
        QualificationRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        Subject $subject,
        Qualification $qualification,
        ApiReturnService $apiReturnService,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $qualification->getId();

        /** @var Qualification $qualification */
        $qualification = $serializer->deserialize(
            json_encode($data),
            Qualification::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($qualification)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->delete($qualification);

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