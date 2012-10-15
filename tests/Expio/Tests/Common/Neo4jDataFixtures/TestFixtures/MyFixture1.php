<?php

namespace TestFixtures;

use Expio\Common\Neo4jDataFixtures\FixtureInterface;
use HireVoice\Neo4j\EntityManager as ObjectManager;

class MyFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
    }
}
