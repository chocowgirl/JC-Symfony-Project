<?php

namespace App\DataFixtures;

use App\Entity\Shoepair;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use function Symfony\Component\Clock\now;

class ShoepairFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        
        for ($i=0; $i<10; $i++){
            $date = new DateTime();
            $date->setDate(2024, 9, $i);

            $shoepair = new Shoepair();
            $shoepair->setNameBrandModel('shoe' . $i);
            $shoepair->setStartDateOfUse($date);
            $shoepair->setWearLimitKm(600+$i);
            $shoepair->setCurrentWearKm(0+$i);
            $shoepair->setShoeNote('shoenote' . $i);
            $shoepair->setInActiveService(true);
            $shoepair->setUserOwner($this->getReference("user" . rand(0,9)));
            $this->addReference("shoepair{$i}", $shoepair);
            $manager->persist($shoepair);
        }
        // $product = new Product();


        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
