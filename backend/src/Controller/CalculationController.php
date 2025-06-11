<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Comment;
use App\Entity\Proof;
use App\Entity\Subject;
use App\Repository\AnswerRepository;
use App\Repository\CalculationRepository;
use App\Repository\CommentRepository;
use App\Repository\ProofRepository;
use App\Repository\QuestionRepository;
use App\Service\ApiErrorsService;
use Exception;
use JMS\Serializer\SerializationContext;
use App\Service\Validator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AnswerController
 *
 * @package App\Controller
 */
class CalculationController extends AbstractController
{

    /**
     * @param SerializerInterface   $serializer
     * @param Subject               $subject
     *
     *
     * @param CalculationRepository $calculationRepository
     *
     * @return Response
     *
     * @Route("/api/calculation/{id}", methods={"GET"}, name="calculation_get_id")
     * @ParamConverter("subject", class="App\Entity\subject")
     * @IsGranted({"ROLE_SUPER_ADMIN"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns subject Calculation.",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Subject::class, groups={"read"})
     *     )
     * )
     * @SWG\Tag(name="calculation")
     *
     * @Areas({"internal"})
     */
    public function getById(
        SerializerInterface $serializer,
        Subject $subject,
        CalculationRepository $calculationRepository
    )
    {
        return new Response(
            $serializer->serialize(
                $calculationRepository->findById($subject),
                'json',
                SerializationContext::create()->setGroups(['read'])
            ), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

}
