<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\User\InvalidTokenException;
use App\Repository\UserRepository;
use App\Service\ApiErrorsService;
use App\Service\User\UserManager;
use Exception;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Swift_Mailer;
use Swift_Message;
use SendGrid\Mail\Mail;
use SendGrid;
use Swift_TransportException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class SecurityController
 *
 * @package App\Controller
 */
class SecurityController
{
    /**
     * @Route("/api/login", methods={"POST"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Log in via the API"
     * )
     *
     * @SWG\Tag(name="security")
     *
     * @Areas({"internal", "default"})
     */
    public function loginAction()
    {
    }

    /**
     * @param UserManager           $manager
     * @param UserRepository        $repository
     * @param Swift_Mailer          $mailer
     * @param Environment           $twig
     * @param Request               $request
     * @param ParameterBagInterface $parameterBag
     *
     * @param ApiErrorsService      $apiErrorsService
     *
     * @return JsonResponse
     *
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @Route("/api/reset-password", methods={"POST"})
     *
     * @SWG\Response(response="200", description="Sent if process completed succesesfully.")
     *
     * @SWG\Tag(name="security")
     *
     * @Areas({"internal", "default"})
     */
    public function resetPasswordAction(
        UserManager $manager,
        UserRepository $repository,
        Swift_Mailer $mailer,
        Environment $twig,
        Request $request,
        ParameterBagInterface $parameterBag,
        ApiErrorsService $apiErrorsService
    )
    {
        $data = json_decode($request->getContent(), true);

        // Validate Request
        if (!array_key_exists('email', $data)) {
            return new JsonResponse(['message' => 'Invalid parameters!'], 400);
        }

        $user = $repository->byEmail($data['email']);


        if (!is_null($user)) {

            if (!$manager->generateToken($user)) {
                return new JsonResponse(['message' => 'An unknown error occurred!'], 500);
            }

            if ($parameterBag->get('DEV_MAIL') === "true") {
                $message = (new Swift_Message('Reset Password Email'))
                    ->setFrom('support@farosian.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $twig->render(
                            'emails/reset_password_email.html.twig',
                            [
                                'resetUrl' => $parameterBag->get('RESET_URL'),
                                'user' => $user
                            ]
                        ),
                        'text/html'
                    );

                try {
                    $mailer->send($message);
                } catch (Swift_TransportException $e) {
                    return '';
                }

                return new JsonResponse(['message' => 'Reset Email sent!'], 200);
            } else {
                $email = new Mail();
                $email->setFrom("support@farosian.com", "Farosian Support");
                $email->setSubject("Reset Password Email");
                $email->addTo($user->getEmail(), "Farosian Support");
                $email->addContent('text/html',
                    $twig->render(
                        'emails/reset_password_email.html.twig',
                        [
                            'resetUrl' => $parameterBag->get('RESET_URL'),
                            'user' => $user
                        ]
                    )
                );
                $sendgrid = new SendGrid(getenv('SENDGRID_API_KEY'));
                try {
                    $sendgrid->send($email);
                } catch (Exception $e) {

                    return '';
                }

                return new JsonResponse(['message' => 'Reset Email sent!'], 200);
            }
        }

        return new JsonResponse(['message' => 'An unknown error occurred!'], 404);
    }

    /**
     * @param Request     $request
     * @param UserManager $manager
     * @param string      $token
     *
     * @return JsonResponse
     *
     * @Route("/api/reset-password/{token}", methods={"POST"})
     *
     * @SWG\Response(response="200", description="Sent if process completed succesesfully.")
     * @SWG\Response(response="400", description="Invalid parameters.")
     * @SWG\Response(response="500", description="Unknown error occurred.")
     * @SWG\Parameter(
     *     name="token",
     *     in="path",
     *     type="string",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="body",
     *     description="JSON object containing the user's email.",
     *     required=true,
     *     in="body",
     *     type="json",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="password",
     *              type="string"
     *         )
     *     )
     * )
     * @SWG\Tag(name="security")
     *
     * @Areas({"internal"})
     */
    public function changePasswordAction(
        Request $request,
        UserManager $manager,
        string $token
    )
    {
        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('password', $data)) {
            return new JsonResponse(['message' => 'Invalid request!'], 400);
        }

        try {
            $manager->resetPassword($token, $data['password']);

            return new JsonResponse(['message' => 'Password changed!'], 200);
        } catch (InvalidTokenException $e) {
            return new JsonResponse(['message' => 'Invalid token!'], 400);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Unknown error ocurred!'], 500);
        }
    }
}