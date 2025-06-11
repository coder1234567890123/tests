<?php

namespace App\Tests\Util;

use App\Entity\Employment;
use App\Entity\Profile;
use App\Entity\Qualification;
use App\Entity\Report;
use App\Exception\InvalidReportTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use App\Entity\Country;
use App\Entity\User;
use App\Entity\Subject;
use App\Entity\Company;

class SubjectEntityTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testSubjectGetterAndSetter()
    {
        echo "\nSubjectEntityTest:  Subject Entity\n";

        $subject = new Subject();
        $user = new User();
        $company = new Company();
        $country = new Country();
        $profile = new Profile();
        $profiles = new ArrayCollection();
        $profiles->add($profile);

        $subject->setCreatedBy($user);
        $this->assertEquals($subject->getCreatedBy(), $user);

        $subject->setCountry($country);
        $this->assertEquals($subject->getCountry(), $country);

        $country->setName('SA');
        $this->assertEquals($subject->getCountryName(), 'SA');

        $subject->setCompany($company);
        $this->assertEquals($subject->getCompany(), $company);

        $company->setName('Company');
        $this->assertEquals($subject->getCompanyName(), 'Company');

        $subject->setIdentification('123456789');
        $this->assertEquals('123456789', $subject->getIdentification());

        $subject->setGender('Male');
        $this->assertEquals('Male', $subject->getGender());

        $date = new \DateTimeImmutable();
        $subject->setDateOfBirth($date);
        $this->assertEquals($date, $subject->getDateOfBirth());

        $subject->setSecondaryMobile('123456789');
        $this->assertEquals('123456789', $subject->getSecondaryMobile());

        $profile->setPlatform('twitter');
        $subject->addProfile($profile);
        $this->assertEquals($profiles, $subject->getProfiles());
        $this->assertEquals(0, count($subject->getFacebookProfiles()));
        $this->assertEquals(1, count($subject->getTwitterProfiles()));
        $this->assertEquals(0, count($subject->getPinterestProfiles()));
        $this->assertEquals(0, count($subject->getInstagramProfiles()));
        $this->assertEquals(0, count($subject->getLinkedinProfiles()));
        $this->assertEquals(0, count($subject->getFlickrProfiles()));
        $this->assertEquals(0, count($subject->getYoutubeProfiles()));
        $this->assertEquals(0, count($subject->getWebSearchProfiles()));

        $qualification = new Qualification();
        $qualifications = new ArrayCollection();
        $qualifications->add($qualification);

        $subject->addQualification($qualification);
        $this->assertEquals($qualifications, $subject->getQualifications());

        $employment = new Employment();
        $employments = new ArrayCollection();
        $employments->add($employment);

        $subject->addEmployments($employment);
        $this->assertEquals($employments, $subject->getEmployments());

        $subject->setRushReport(true);
        $this->assertEquals(true, $subject->isRushReport());

        $subject->setStatus('new');
        $this->assertEquals('new', $subject->getStatus());

        $subject->setReportType('full');
        $this->assertEquals('full', $subject->getReportType());

        $this->expectException(InvalidReportTypeException::class);
        $subject->setReportType('tes');

        $report = new Report();
        $reports = new ArrayCollection();
        $reports->add($report);

        $subject->addReport($report);
        $this->assertEquals($reports, $subject->getReports());

        $subject->setFirstName('bob');
        $this->assertEquals('bob', $subject->getFirstName());

        $subject->setMiddleName('bob');
        $this->assertEquals('bob', $subject->getMiddleName());

        $subject->setLastName('bob');
        $this->assertEquals('bob', $subject->getLastName());

        $subject->setMaidenName('bob');
        $this->assertEquals('bob', $subject->getMaidenName());

        $subject->setNickname('bob');
        $this->assertEquals('bob', $subject->getNickname());

        $subject->setHandles(["test","array"]);
        $this->assertEquals(["test","array"], $subject->getHandles());

        $subject->setPrimaryEmail('test@example.com');
        $this->assertEquals('test@example.com', $subject->getPrimaryEmail());

        $subject->setSecondaryEmail('test@example.com');
        $this->assertEquals('test@example.com', $subject->getSecondaryEmail());

        $subject->setEducationInstitutes(["test","array"]);
        $this->assertEquals(["test","array"], $subject->getEducationInstitutes());

        $subject->setProvince('Eastern Cape');
        $this->assertEquals('Eastern Cape', $subject->getProvince());

        $subject->setEnabled(true);
        $this->assertEquals(true, $subject->isEnabled());

        $subject->setImageFile('1.jpg');
        $this->assertEquals('1.jpg', $subject->getImageFile());

    }
}
