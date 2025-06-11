<?php declare(strict_types=1);

namespace App\Exception;

use App\Entity\UserTracking;

/**
 * Class InvalidTrackingActionException
 *
 * @package App\Exception
 */
class InvalidTrackingActionException extends \Exception
{
    /**
     * InvalidTrackingActionException constructor.
     */
    public function __construct()
    {
        $validTrackingActions = implode(', ', UserTracking::ACTION_TYPES);
        parent::__construct("Invalid action specified. Valid actions: '$validTrackingActions'");
    }
}