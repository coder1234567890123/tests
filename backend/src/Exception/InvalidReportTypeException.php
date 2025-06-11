<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\Subject;

/**
 * Class InvalidReportTypeException
 *
 * @package App\Exception
 */
class InvalidReportTypeException extends \Exception
{
    /**
     * InvalidReportTypeException constructor.
     */
    public function __construct()
    {
        $validReportTypes = implode(', ', Subject::REPORT_TYPES);
        parent::__construct("Invalid report type specified. Valid report types: '$validReportTypes'");
    }
}