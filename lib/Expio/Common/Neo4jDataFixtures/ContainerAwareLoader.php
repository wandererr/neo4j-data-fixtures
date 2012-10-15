<?php

namespace Expio\Common\Neo4jDataFixtures;

use Expio\Common\Neo4jDataFixtures\FixtureInterface;
use Expio\Common\Neo4jDataFixtures\Loader;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine data fixtures loader that injects the service container into
 * fixture objects that implement ContainerAwareInterface.
 *
 * Note: Use of this class requires the Doctrine data fixtures extension, which
 * is a suggested dependency for Symfony.
 */
class ContainerAwareLoader extends Loader
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function addFixture(FixtureInterface $fixture)
    {
        if ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer($this->container);
        }

        parent::addFixture($fixture);
    }
}
