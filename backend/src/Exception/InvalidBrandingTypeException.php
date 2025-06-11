<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\Report;

/**
 * Class InvalidBrandingTypeException
 *
 * @package App\Exception
 */
class InvalidBrandingTypeException extends \Exception
{
    /**
     * InvalidBrandingTypeException constructor.
     */
    public function __construct()
    {
        $validBrandingTypes = implode(', ', Report::BRANDING_TYPES);
        parent::__construct("Invalid branding type specified. Valid answer types: '$validBrandingTypes'");
    }
}