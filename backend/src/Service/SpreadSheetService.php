<?php
/**
 * Created by PhpStorm.
 * User: kuda
 * Date: 8/6/19
 * Time: 8:15 AM
 */

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserTracking;
use App\Entity\BundleUsed;
use App\Repository\ReportingRepository;
use App\Repository\UserTrackingRepository;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Exception;

/**
 * Class SpreadSheetService
 *
 * @package App\Service
 */
class SpreadSheetService
{
    /**
     * @var WriterEntityFactory
     */
    private $writer;

    /**
     * @var ReportingRepository
     */
    private $reportingRepository;

    /**
     * @var UserTrackingRepository
     */
    private $userTrackingRepository;

    /**
     * SpreadSheetService constructor.
     *
     * @param ReportingRepository    $reportingRepository
     * @param UserTrackingRepository $userTrackingRepository
     */
    public function __construct(ReportingRepository $reportingRepository, UserTrackingRepository $userTrackingRepository)
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();
        $this->reportingRepository = $reportingRepository;
        $this->userTrackingRepository = $userTrackingRepository;

        /** Create a style with the StyleBuilder */
        $this->style = (new StyleBuilder())
            ->setShouldWrapText()
            ->build();
    }

    /**
     *Start creation of excel export
     *
     * @param Report [] $reports
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function exportFilterReportQueues($reports, $role)
    {
        // Create a Temporary file in the system
        $fileName = 'Export_Reports_' . date("dmYHis") . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headerPart1 = [
            'Date Received',
            'Work Request Type',
            'Company',
            'Sent By',
            'Analyst',
            'Status',
            'Approved By',
            'Date Completed',
            'Subject',
            'ID/ Passport number',
            'Country',
            'Province/ state',
            'Gender',
            ''
        ];

        if ($role === 'ROLE_SUPER_ADMIN') {
            $headerPart2 = [
                'Creativity',
                'Professional Engagement',
                'Communication',
                'Network reach',
                'Business writing',
                'Network engagement',
                'Professional image',
                'Team work',
                'Social media score',
                'Risk score',
                '',
            ];
        } else {
            $headerPart2 = [];
        }

        if ($role === 'ROLE_SUPER_ADMIN') {
            $headerScoresOverwrite = $this->scoresOverwriteHeader();
            $headerFacebook = $this->socialMediaHeader('Facebook');
            $headerTwitter = $this->socialMediaHeader('Twitter');
            $headerLinkedin = $this->socialMediaHeader('Linkedin');
            $headerInstagram = $this->socialMediaHeader('Instagram');
            $headerGoogle = $this->socialMediaHeader('Google');
            $headerPinterest = $this->socialMediaHeader('Pinterest');
            $headerFlickr = $this->socialMediaHeader('Flickr');
            $headerYoutube = $this->socialMediaHeader('Youtube');
        } else {
            $headerScoresOverwrite = [];
            $headerFacebook = [];
            $headerTwitter = [];
            $headerLinkedin = [];
            $headerInstagram = [];
            $headerGoogle = [];
            $headerPinterest = [];
            $headerFlickr = [];
            $headerYoutube = [];
        }

        $headers = array_merge(
            $headerPart1,
            $headerPart2,
            $headerScoresOverwrite,
            $headerFacebook,
            $headerTwitter,
            $headerLinkedin,
            $headerInstagram,
            $headerGoogle,
            $headerPinterest,
            $headerFlickr,
            $headerYoutube
        );

        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        $zebraBlackStyle = (new StyleBuilder())
            ->setBackgroundColor(Color::BLACK)
            ->setFontColor(Color::WHITE)
            ->setFontSize(10)
            ->build();

        foreach ($reports as $report) {
            $row = [];

            $row[] = $report['created_at']->format('d/m/Y H:i');
            $row[] = $report['request_type'];
            $row[] = $report['company_name'];
            $row[] = '';
            $row[] = $report['assigned_to'];
            $row[] = $report['status'];
            $row[] = $report['approved_by'];
            $row[] = $report['date_completed'] ? $report['date_completed']->format('d/m/Y H:i') : 'Not Completed';
            $row[] = $report['subject']['first_name'] . ' ' . $report['subject']['last_name'];
            $row[] = $report['identification'];
            $row[] = $report['country'];
            $row[] = $report['province'];
            $row[] = $report['gender'];

            $row[] = '';

            if ($role === 'ROLE_SUPER_ADMIN') {
                $row[] = floatval($report['overall_behavior_scores']['creativity']);
                $row[] = floatval($report['overall_behavior_scores']['professional_engagement']);
                $row[] = floatval($report['overall_behavior_scores']['communication_skills']);
                $row[] = floatval($report['overall_behavior_scores']['network_reach']);
                $row[] = floatval($report['overall_behavior_scores']['business_writing_ability']);
                $row[] = floatval($report['overall_behavior_scores']['network_engagement']);
                $row[] = floatval($report['overall_behavior_scores']['professional_image']);
                $row[] = floatval($report['overall_behavior_scores']['teamwork_collaboration']);
                $row[] = floatval($report['weighted_social_media_score']);
                $row[] = floatval($report['risk_score']);

                $row[] = '';
                $row[] = floatval($report['scores_overwritten']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['creativity']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['professional_engagement']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['communication_skills']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['network_reach']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['business_writing_ability']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['network_engagement']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['professional_image']);
                $row[] = floatval($report['overall_behavior_scores_overwritten']['teamwork_collaboration']);
                $row[] = floatval($report['weighted_social_media_score_overwritten']);
                $row[] = floatval($report['risk_score_overwritten']);

                foreach ($this->socialMedia($report, 'facebook') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'twitter') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'linkedin') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'instagram') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'web') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'pinterest') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'flickr') as $socialMedia) {
                    $row[] = $socialMedia;
                }

                foreach ($this->socialMedia($report, 'youtube') as $socialMedia) {
                    $row[] = $socialMedia;
                }
            }
            $rowFromValues = WriterEntityFactory::createRowFromArray($row);
            $this->writer->addRow($rowFromValues);
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }


    /**
     * @param $platform
     *
     * @return array
     */
    private function socialMediaHeader($platform): array
    {
        return [
            '',
            $platform,
            'Account',
            'Positive content',
            'Activity',
            'Negative content',
            'Privacy settings',
            'Multiple accounts',
            'Friends',
            'Disclosure',
            'Unweighted platform score',
            'Weighted platform score'
        ];
    }


    /**
     * @return array
     */
    private function scoresOverwriteHeader()
    {
        return [
            'Overwritten Scores',
            'Creativity',
            'Professional Engagement',
            'Communication',
            'Network reach',
            'Business writing',
            'Network engagement',
            'Professional image',
            'Team work',
            'Social media score',
            'Risk score'
        ];
    }

    /**
     * @param $report
     * @param $platform
     *
     * @return array
     */
    private function socialMedia($report, $platform): array
    {
        return [
            '',
            $platform,

            $report[$platform]['account'],
            $report[$platform]['positive_content'],
            $report[$platform]['activity'],
            $report[$platform]['negative_content'],
            $report[$platform]['privacy_settings'],
            $report[$platform]['multiple_accounts'],
            $report[$platform]['friends'],
            $report[$platform]['disclosure'],
            $report[$platform]['unweighted_platform_score'],
            $report[$platform]['weighted_platform_score']
        ];
    }

    /**
     * @param Report $report
     *
     * @return string
     */
    private function findLastApprovedBy(Report $report)
    {
        switch ($report->getStatus()) {
            case 'new_request':
                return 'No Approval';
                break;
            case 'search_started':
            case 'search_completed':
            case 'validated':
            case 'investigation_completed':
            case 'under_investigation':
                if ($report->getRequestType() === 'normal') {
                    return 'No Approval';
                } else {
                    $tracking = $this->userTrackingRepository->getApprovedBy($report->getId(), 'report_type_approved');
                    return $tracking->getUser()->getFullName();
                }
                break;
            case 'report_type_approved':
            case 'team_lead_approved':
            case 'completed':
                $tracking = $this->userTrackingRepository->getApprovedBy($report->getId(), $report->getStatus());
                return $tracking->getUser()->getFullName();
                break;
        }
    }

    /**
     * @param User            $user |null
     * @param UserTracking [] $tracks
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    public function exportUserReport(User $user = null, $tracks)
    {
        // Create a Temporary file in the system
        $fileName = $user ? $user->getFirstName() . '_' . $user->getLastName() . '_' . date("dmYHis") . '.xlsx' : 'user_tracking.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headers = ['Date', 'Time', 'User', 'User Type', 'Company Name', 'Source', 'Action'];
        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        foreach ($tracks as $track) {
            $row = [];
            $row[] = trim($track->getCreatedAt()->format('d/m/Y'));
            $row[] = trim($track->getCreatedAt()->format('H:i'));
            $row[] = trim($track->getUser()->getFullName());
            $row[] = trim($this->getRole($track->getUser()));
            $row[] = trim($this->getCompany($track));
            $row[] = trim(str_replace('-', ' ', $track->getSource()));
            $row[] = trim(str_replace('_', ' ', $track->getAction()));
            $rowFromValues = WriterEntityFactory::createRowFromArray($row, $this->style);
            $this->writer->addRow($rowFromValues);
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }

    /**
     * @param string        $companyName
     * @param BundleUsed [] $bundles
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    public function exportCompanyBundles(string $companyName, $bundles)
    {
        // Create a Temporary file in the system
        $fileName = $companyName . '_' . date("dmYHis") . '_bundlesUsed.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headers = ['Date', 'Subject', 'Company Product', 'Unit Usage'];
        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        foreach ($bundles as $bundle) {
            $row = [];
            $row[] = $bundle->getCreatedAt()->format('d/m/Y');
            $row[] = $bundle->getSubject()->getFirstName() . ' ' . $bundle->getSubject()->getLastName();
            $row[] = $bundle->getCompanyProduct()->getProductType();
            $row[] = $bundle->getAddUnit() ? $bundle->getAddUnit() . ' unit added' : $bundle->getAddUnit() . ' unit rejected';
            $rowFromValues = WriterEntityFactory::createRowFromArray($row);
            $this->writer->addRow($rowFromValues);
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }

    /**
     * @param string      $companyName
     * @param Accounts [] $accounts
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    public function exportAccounts(string $companyName, $accounts)
    {
        // Create a Temporary file in the system
        $fileName = $companyName . '_' . date("dmYHis") . '_bundlesUsed.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headers = [
            'Company',
            'Monthly Amount',
            'Subject',
            'Request Type',
            'Unit Used',
            'Total Bundle Used',
            'Bundle Add',
            'Total Bundle Added',
            'Reset Monthly',
            'Date'
        ];

        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        foreach ($accounts as $account) {
            $row = [];

            $row[] = $account['company']['name'];
            $row[] = $account['monthly_units'];
            $row[] = $account['subject']['first_name'] . ' ' . $account['subject']['last_name'];
            $row[] = $account['request_type'];
            $row[] = $account['unit_used'];
            $row[] = $account['unit_used'];
            $row[] = $account['total_units_used'];
            $row[] = $account['add_unit'];
            $row[] = $account['reset_monthly_amounts'];
            $row[] = $account['created_at']->format('d/m/Y');
            $rowFromValues = WriterEntityFactory::createRowFromArray($row);
            $this->writer->addRow($rowFromValues);
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }

    /**
     * @param UserTracking $track
     *
     * @return string
     */
    private function getCompany(UserTracking $track)
    {
        $company = 'No Company';
        if ($track->getCompany()) {
            $company = $track->getCompany()->getName();
        } else if ($track->getSubject() && $track->getSubject()->getCompany()) {
            $track->getSubject()->getCompany()->getName();
        } else if ($track->getUser() && $track->getUser()->getCompany()) {
            $company = $track->getUser()->getCompany()->getName();
        }
        return $company;
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function getRole(User $user)
    {
        if (key_exists(2, explode('_', $user->getRoles()[0]))) {
            $role = explode('_', $user->getRoles()[0])[1] . ' ' . explode('_', $user->getRoles()[0])[2];
        } else {
            $role = explode('_', $user->getRoles()[0])[1];
        }

        return $role;
    }

    /**
     * @return Style
     */
    private function getHeaderStyle()
    {
        /** Create a style with the StyleBuilder */
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(15)
            ->setShouldWrapText()
            ->build();

        return $style;
    }

    /**
     * @param Report [] $reports
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function exportDeliveryTracking($reports)
    {
        // Create a Temporary file in the system
        $fileName = 'Export_Reports_' . date("dmYHis") . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headers = ['Subject Name', 'Risk Score', 'Nature of High Risk', 'Date & Time Submitted', 'Due Date', 'Date & Time Delivered', 'Allocated to'];
        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        foreach ($reports as $report) {
            if ($report->getStatus() === 'completed') {
                $row = [];
                $row[] = $report->getSubjectName();
                $row[] = $report->getRiskScore();
                $row[] = $report->getRisk();
                $row[] = $report->getCreatedAt()->format('d/m/Y H:i');
                $row[] = $report->getDueDate() ? $report->getDueDate()->format('d/m/Y') : 'No Due Date';
                $row[] = $report->getCompletedDate()->format('d/m/Y');
                $row[] = $report->getAssignedToName();
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $this->writer->addRow($rowFromValues);
            }
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }

    /**
     * @param Report [] $reports
     *
     * @return array
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function exportUsageReport($reports)
    {
        // Create a Temporary file in the system
        $fileName = 'Export_Reports_' . date("dmYHis") . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $this->writer->openToFile($temp_file); // write data to a file or to a PHP stream

        /** Shortcut: add a row from an array of values */
        $headers = ['Subject Name', 'Report Type', 'Rush Report Ind', 'Company Name', 'Date & Time Submitted', 'Due Date', 'Date & Time Delivered', 'Allocated to'];
        $rowFromHeaders = WriterEntityFactory::createRowFromArray($headers, $this->getHeaderStyle());
        $this->writer->addRow($rowFromHeaders);

        foreach ($reports as $report) {
            if ($report->getStatus() === 'completed') {
                $row = [];
                $row[] = $report->getSubjectName();
                $row[] = $report->getSubject()->getReportType();
                $row[] = $report->getRequestType() === 'rush' ? 'Yes' : 'No';
                $row[] = $report->getSubject()->getCompanyName();
                $row[] = $report->getCreatedAt()->format('d/m/Y H:i');
                $row[] = $report->getDueDate() ? $report->getDueDate()->format('d/m/Y') : 'No Due Date';
                $row[] = $report->getCompletedDate()->format('d/m/Y');
                $row[] = $report->getAssignedToName();
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $this->writer->addRow($rowFromValues);
            }
        }

        $this->writer->close();
        return ['path' => $temp_file, 'name' => $fileName];
    }
}