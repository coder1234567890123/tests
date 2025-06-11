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
        echo "\nProofStorageEntityTest:  ProofStorage Entity \n";

        $proofstorage = new ProofStorage();
        $user = new User();
        $subject = new Subject();

        $proofstorage->setCreatedBy($user);
        $this->assertEquals($proofstorage->getCreatedBy(), $user);

        $proofstorage->setSubject($subject);
        $this->assertEquals($proofstorage->getSubject(), $subject);

        $subject->setFirstName('test');
        $this->assertEquals($proofstorage->getSubjectName(), 'test');

        $proofstorage->setImageFile('image_file.jpg');
        $this->assertEquals('image_file.jpg', $proofstorage->getImageFile());



    }
}
