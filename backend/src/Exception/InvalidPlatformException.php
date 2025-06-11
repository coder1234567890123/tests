<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\Profile;

/**
 * Class InvalidPlatformException
 *
 * @package App\Exception
 */
class InvalidPlatformException extends \Exception
{
    /**
     * InvalidPlatformException constructor.
     */
    public function __construct()
    {
        $validPlatforms = implode(', ', Profile::PLATFORMS);
        parent::__construct("Invalid platform specified. Valid platforms: $validPlatforms");
    }
}