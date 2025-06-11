<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Service\SearchPhrase\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 *
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @param Parser $parser
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/")
     */
    public function indexAction(Parser $parser)
    {

        return $this->redirect("/api/doc");
    }
}