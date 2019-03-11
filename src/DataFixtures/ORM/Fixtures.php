<?php

namespace DataFixtures\ORM;

use BlogBundle\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */

    public function load(ObjectManager $manager)
    {
        $roleU = new Role();
        $roleU
            ->setName('ROLE_USER');
        $manager->persist($roleU);

        $roleA = new Role();
        $roleA
            ->setName('ROLE_USER');
        $manager->persist($roleA);
        $manager->flush();
    }
}