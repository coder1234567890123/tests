<?php

namespace App\Service;

use App\Repository\MessageSystemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MessageService
 * @package App\Service
 */
class MessageService
{
    /**
     * @var MessageSystemRepository
     */
    private $messageSystemRepository;

    /**
     * MessageService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $token
     * @param ParameterBagInterface $params
     * @param MessageSystemRepository $messageSystemRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params
    )
    {
        $this->entityManager = $entityManager;

    }

    /**
     * @param $report
     *
     * @return string
     */
    public function messages($report)
    {

        if ($report) {
            switch ($report->getRequestType()) {
                case 'normal':
                    return "Normal Report - for: " . $report->getSubject()->getFirstName() . '' . $report->getSubject()->getLastName();
                    break;

                case 'rush':
                    return "Rush Report - for: " . $report->getSubject()->getFirstName() . '' . $report->getSubject()->getLastName();
                    break;

                case 'test':
                    return "Test Report - for: " . $report->getSubject()->getFirstName() . '' . $report->getSubject()->getLastName();
                    break;

                default:
                    return 'Unknown Message type';
            }
        } else {
            return 'Message Error';
        }
    }

    /**
     * @param $report
     *
     * @return string
     */
    public function messagesHeader($report)
    {

        if ($report) {
            switch ($report->getStatus()) {
                case 'new_request':
                    return "New Requests - " . $report->getRequestType();
                    break;

                case 'needs_approval':
                    return "Needs Approval - " . $report->getRequestType();
                    break;

                case 'report_type_approved':
                    return "Report Approved - " . $report->getRequestType();
                    break;

                default:
                    return 'Message';
            }
        } else {
            return 'Message Error';
        }
    }
}