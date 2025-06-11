<?php

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;  
use Doctrine\ORM\Query\Filter\SQLFilter;  
use Doctrine\Common\Annotations\Reader;

class ActiveFilter extends SQLFilter  
{
    protected $reader;

    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (empty($this->reader)) {
            return '';
        }

        // The Doctrine filter is called for any query on any entity
        // Check if the current entity is "is active" (marked with an annotation) 
        $isActive = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(), // class annotation
            'App\\Annotation\\IsEnabled'
        );

        if ($targetEntity->hasField('enabled') && $isActive) {
            //property exists

            $fieldName = $isActive->field;

            try {
                // Don't worry, getParameter automatically quotes parameters
                $enabled = $this->getParameter('enabled');
                $isIgnore = (int) str_replace("'", "", $this->getParameter('ignore'));
            } catch (\InvalidArgumentException $e) {
                // No value has been defined
                return '';
            }

            if (empty($fieldName) || empty($enabled) || $isIgnore === 1) {
                return '';
            }

            $query = sprintf('%s.%s = %s', $targetTableAlias, $fieldName, $enabled);

            return $query;
        }

        return '';

    }
}