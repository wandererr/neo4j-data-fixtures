<?php

namespace Expio\Tests\Common\Neo4jDataFixtures\TestFixtures;

use Expio\Common\Neo4jDataFixtures\SharedFixtureInterface;
use Expio\Common\Neo4jDataFixtures\ReferenceRepository;
use Expio\Tests\Common\Neo4jDataFixtures\TestEntity\Role;
use HireVoice\Neo4j\EntityManager as ObjectManager;

class RoleFixture implements SharedFixtureInterface
{
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function load(ObjectManager $manager)
    {
        $adminRole = new Role();
        $adminRole->setName('admin');

        $manager->persist($adminRole);
        $this->referenceRepository->addReference('admin-role', $adminRole);
        $manager->flush();
    }
}
