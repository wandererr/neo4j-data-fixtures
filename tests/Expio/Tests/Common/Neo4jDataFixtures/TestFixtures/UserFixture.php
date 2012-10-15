<?php
namespace Expio\Tests\Common\Neo4jDataFixtures\TestFixtures;

use Expio\Common\Neo4jDataFixtures\AbstractFixture;
use Expio\Tests\Common\Neo4jDataFixtures\TestEntity\User;
use HireVoice\Neo4j\EntityManager as ObjectManager;

class UserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new User;
        $admin->setId(4);
        $admin->setCode('007');
        $admin->setEmail('admin@example.com');
        $admin->setPassword('secret');
        $role = $this->getReference('admin-role');
        $admin->setRole($role);

        $manager->persist($admin);
        $manager->flush();

        $this->addReference('admin', $admin);
    }
}
