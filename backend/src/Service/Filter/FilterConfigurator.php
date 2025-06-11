<?php

namespace App\Service\Filter;
 
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;  
use Doctrine\Common\Persistence\ObjectManager;  
use Doctrine\Common\Annotations\Reader;

class FilterConfigurator  
{
    protected $em;
    protected $tokenStorage;
    protected $reader;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage, Reader $reader)
    {
        $this->em              = $em;
        $this->tokenStorage    = $tokenStorage;
        $this->reader          = $reader;
    }

    public function onKernelRequest()
    {
        $activeFilter = $this->em->getFilters()->enable('active_filter');
        $activeFilter->setParameter('enabled', true);
        $activeFilter->setParameter('ignore', 0);
        $activeFilter->setAnnotationReader($this->reader);
    }
}