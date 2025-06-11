<?php

namespace App\Tests\Util;

use App\Entity\Address;
use App\Entity\Employment;
use App\Entity\Country;
use App\Entity\ProofStorage;
use App\Entity\Subject;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ProofStorageEntityTest extends TestCase
{
    public function testEmploymentGetterAndSetter()
    {
        $proofstorage = new ProofStorage();
        $user = new User();
        $subject = new Subject();

        $proofstorage->setCreatedBy($user);
        $this->assertEquals($proofstorage->getCreatedBy(), $user);

        $proofstorage->setSubject($subject);
        $this->assertEquals($proofstorage->getSubject(), $subject);

        $proofstorage->setImageFile('image_file.jpg');
        $this->assertEquals('image_file.jpg', $proofstorage->getImageFile());



    }
}
