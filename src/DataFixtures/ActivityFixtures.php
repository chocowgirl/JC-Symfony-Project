<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActivityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<10; $i++){
            $date = new DateTime();
            $date->setDate(2024, 9, $i);

            $activity = new Activity();
            $activity->setActivityDate($date);
            $activity->setActivityDistanceKm(5 + $i);
            $activity->setActivityNote("");
            $activity->setActivityChronoMin(30 + 2*$i);
            $activity->setUser($this->getReference("user" . rand(0,9)));
            $activity->setShoepairUsed($this->getReference("shoepair" . rand(0,9)));
            $manager->persist($activity);
        }
        // $product = new Product();

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class, ShoepairFixtures::class];
        // Note: UserFixtures::class n'est pas necessaire ici car c'est déjà cité en ShoepairFixtures
    }
}
