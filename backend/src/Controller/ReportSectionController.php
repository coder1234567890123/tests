<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\ReportSection;
use App\Repository\ReportSectionRepository;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ReportSectionController
 *
 * @package App\Controller
 */
class ReportSectionController extends AbstractController
{
    /**
     * @param ReportSectionRepository   $repository
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     *
     * @Route("/api/report_section", methods={"GET"}, name="report_section_get")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get a paginated list of report sections",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=ReportSection::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function getAction(
        ReportSectionRepository $repository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        // Get Parameters
        $page       = (int)$request->get('page', 1);
        $limit      = (int)$request->get('limit', 10);
        $descending = $request->get('descending', false);
        $descending = $descending == 'true' ? true : false;
        $sort       = $request->get('sort', 'name');
        $search     = $request->get('search', '');

        // Configure Pagination
        $count    = $repository->count();
        $offset   = ($page - 1) * $limit;
        $reportSections = $repository->paginated($offset, $limit, $sort, $descending, $search);
        $pages    = (int)ceil($count / $limit);

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation(
                $reportSections,
                'report_sections',
                'report_section'
            ),
            'report_section_get',
            [],
            $page,
            $limit,
            $pages,
            'page',
            'limit',
            false,
            $count
        );

        return new Response(
            $serializer->serialize(
                $paginatedCollection,
                'json',
                SerializationContext::create()->setGroups(['Default', 'report_sections' => ['read']])
            ),200,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param Validator           $validator
     * @param ReportSectionRepository   $repository
     *
     * @return Response
     * @throws \Exception
     * @Route("/api/report_section", methods={"POST"}, name="report_section_post")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Posts to ReportSection.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportSection::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function postAction(
        ReportSectionRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request
    )
    {
        /** @var ReportSection $reportSection */
        $reportSection = $serializer->deserialize(
            $request->getContent(),
            ReportSection::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($reportSection)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($reportSection);

            return new Response(
                $serializer->serialize(
                    $reportSection,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param ReportSectionRepository $repository
     * @param SerializerInterface $serializer
     * @param Validator $validator
     * @param Request $request
     * @param ReportSection $reportSection
     *
     * @return Response
     *
     * @Route("/api/report_section/{id}", methods={"PATCH"}, name="report_section_update")
     * @ParamConverter("report_section", class="App\Entity\ReportSection")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Update the report section entity.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportSection::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function updateAction(
        ReportSectionRepository $repository,
        SerializerInterface $serializer,
        Validator $validator,
        Request $request,
        ReportSection $reportSection
    )
    {

        $data = json_decode($request->getContent(), true);
        $data['id'] = $reportSection->getId();

        /** @var ReportSection $reportSection */
        $reportSection = $serializer->deserialize(
            json_encode($data),
            ReportSection::class,
            'json',
            DeserializationContext::create()->setGroups(['write'])
        );

        /** @var JsonResponse $response */
        if (($response = $validator->validate($reportSection)) !== false) {
            return $response;
        }

        // Valid Entity
        try {
            $repository->save($reportSection);

            return new Response(
                $serializer->serialize(
                    $reportSection,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @param ReportSection             $reportSection
     *
     * @return Response
     *
     * @Route("/api/report_section/{id}", methods={"GET"}, name="report_section_get_id")
     * @ParamConverter("report_section", class="App\Entity\ReportSection")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a specific report section.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportSection::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function getIDAction(SerializerInterface $serializer, ReportSection $reportSection)
    {
        return new Response(
            $serializer->serialize(
                $reportSection,
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param ReportSectionRepository $repository
     * @param SerializerInterface $serializer
     * @param Validator $validator
     * @param Request $request
     * @param ReportSection $reportSection
     *
     * @return Response
     *
     * @Route("/api/report_section/{id}", methods={"delete"}, name="report_section_delete")
     * @ParamConverter("report_section", class="App\Entity\ReportSection")
     *
     * @SWG\Response(
     *     response="200",
     *     description="detele to report section.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportSection::class, groups={"write"})
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function deleteAction(
        ReportSectionRepository $repository,
        SerializerInterface $serializer,
        ReportSection $reportSection
    )
    {
        try {
            $repository->disable($reportSection);

            return new Response(
                $serializer->serialize(
                    $reportSection,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param ReportSectionRepository $repository
     * @param SerializerInterface $serializer
     * @param ReportSection $reportSection
     *
     * @return Response
     *
     * @Route("/api/report_section/{id}/enable", methods={"PUT"}, name="report_section_enable")
     * @ParamConverter("report_section", class="App\Entity\ReportSection")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Enables a report section",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=ReportSection::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="report_section")
     */
    public function enableAction(
        ReportSectionRepository $repository,
        SerializerInterface $serializer,
        ReportSection $reportSection
    )
    {
        try {
            $repository->enable($reportSection);

            return new Response(
                $serializer->serialize(
                    $reportSection,
                    'json',
                    SerializationContext::create()->setGroups(["read"])
                ),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
