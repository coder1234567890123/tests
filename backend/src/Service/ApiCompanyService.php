<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Comment;
use App\Entity\Company;
use App\Entity\DefaultBranding;
use App\Entity\IdentityConfirm;
use App\Entity\Proof;
use App\Entity\ProofStorage;
use App\Entity\Question;
use App\Entity\Profile;
use App\Repository\DefaultBrandingRepository;
use App\Service\PdfProofService;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use phpDocumentor\Reflection\Types\Context;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ApiTeamsService
 *
 * @package App\Service
 */
class ApiCompanyService
{

    /**
     * ProfileRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param \App\Service\PdfProofService $pdfProofService
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->repositoryCompany = $entityManager->getRepository(Company::class);
    }

    /**
     * @param $company
     *
     * @return array
     */
    public function companyIndex($company)
    {
        $response = [];
        if ($company) {
            foreach ($company as $getData) {
                $response[] = [
                    'id' => $getData->getId(),
                    'name' => $getData->getName(),
                    'city' => $getData->getCity(),
                    'province' => $getData->getProvince(),
                    'created_by' => $getData->getCreatedByName(),
                    'created_at' => $getData->getCreatedDate()
                ];
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $user
     *
     * @return array|null
     */
    public function myCompany($user)
    {
        return $this->getCompany($user->getCompany());
    }

    /**
     * @param $company
     *
     * @return array
     */
    public function getCompany($company): ?array
    {
        $getCompany = $this->repositoryCompany->findBy([
            'id' => $company
        ]);

        $response = [];

        if ($company) {
            foreach ($getCompany as $getData) {
                $response = [
                    'id' => $getData->getId(),
                    'name' => $getData->getName(),
                    'registration_number' => $getData->getRegistrationNumber(),
                    'vat_number' => $getData->getVatNumber(),
                    'company_types' => $getData->getCompanyTypes(),
                    'tel_number' => $getData->getTelNumber(),
                    'fax_number' => $getData->getFaxNumber(),
                    'mobile_number' => $getData->getMobileNumber(),
                    'website' => $getData->getWebsite(),
                    'email' => $getData->getEmail(),
                    'street1' => $getData->getStreet1(),
                    'street2' => $getData->getStreet2(),
                    'suburb' => $getData->getSuburb(),
                    'postal_code' => $getData->getPostalCode(),
                    'province' => $getData->getProvince(),
                    'city' => $getData->getCity(),
                    'note' => $getData->getNote(),
                    'allow_trait' => $getData->isAllowTrait(),
                    'country' => $getData->getCountry(),
                    'contact_firstname' => $getData->getContactFirstName(),
                    'contact_lastname' => $getData->getContactLastName(),
                    'contact_telephone' => $getData->getContactTelephone(),
                    'contact_email' => $getData->getContactEmail(),
                    'account_holder_first_name' => $getData->getAccountHolderFirstName(),
                    'account_holder_last_name' => $getData->getAccountHolderLastName(),
                    'account_holder_phone' => $getData->getAccountHolderPhone(),
                    'account_holder_email' => $getData->getAccountHolderEmail(),
                    'enabled' => $getData->isEnabled(),
                    'image_file' => $getData->getImageFile(),
                    'archived' => $getData->isArchived(),
                    'theme_color' => $getData->getThemeColor(),
                    //'theme_color_2' => $this->checkValue($getData->getThemeColorSecond()),
                    'theme_color_second' => $this->checkValue($getData->getThemeColorSecond()),
                    'theme_color_overlay_rgb' => $this->createRga($getData->getThemeColor()),
                    'footer_link' => $getData->getFooterLink(),
                    'image_cover_logo' => $this->checkValue($getData->getCoverLogo()),
                    'image_footer_logo' => $this->checkValue($getData->getImageFooterLogo()),
                    'image_front_page' => $this->checkValue($getData->getImageFrontPage()),
                    'password_set' => $getData->isPasswordSet(),
                    'use_disclaimer' => $getData->isUseDisclaimer(),
                    'disclaimer' => $getData->getDisclaimer(),
                    'branding_type' => $getData->getBrandingType(),
                    'created_at' => $getData->getCreatedAt(),
                    'created_by' => $getData->getCreatedBy()->getFullName(),
                ];
            }

            return $response;
        } else {
            return [];
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function checkValue($value)
    {
        if ($value) {
            return $value;
        } else {
            return '';
        }
    }


    /**
     * @param $hex
     * @return string
     */
    private function createRga($hex)
    {
        if ($hex) {

            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            return "rgba($r, $g, $b, 0.5)";
        } else {
            return '';
        }
    }
}
