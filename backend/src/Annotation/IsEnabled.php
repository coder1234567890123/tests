<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Anotation to mark entities for soft delete (enabled/disabled)
 * @Annotation
 * @Target("CLASS")
 */
final class IsEnabled  
{
    public $field;
}