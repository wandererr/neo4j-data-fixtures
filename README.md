# Neo4j Data Fixtures Extension

This extension aims to provide a simple way to manage and execute the loading of data fixtures
for the Neo4j OGM. You can write fixture classes by implementing the
Expio\Common\Neo4jDataFixtures\FixtureInterface interface:

    namespace MyDataFixtures;

    use Kwattro\Neo4j\GraphManager;
    use Expio\Common\Neo4jDataFixtures\FixtureInterface;

    class LoadUserData implements FixtureInterface
    {
        public function load(GraphManager $manager)
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');

            $manager->persist($user);
            $manager->flush();
        }
    }

Now you can begin adding the fixtures to a loader instance:

    use Expio\Common\Neo4jDataFixtures\Loader;
    use MyDataFixtures\LoadUserData;

    $loader = new Loader();
    $loader->addFixture(new LoadUserData);

You can load a set of fixtures from a directory as well:

    $loader->loadFromDirectory('/path/to/MyDataFixtures');

You can get the added fixtures using the getFixtures() method:

    $fixtures = $loader->getFixtures();

Now you can easily execute the fixtures:

    use Expio\Common\Neo4jDataFixtures\Executor\OGMExecutor;
    use Expio\Common\Neo4jDataFixtures\Purger\OGMPurger;

    $purger = new OGMPurger();
    $executor = new OGMExecutor($em, $purger);
    $executor->execute($loader->getFixtures());

If you want to append the fixtures instead of purging before loading then pass true
to the 2nd argument of execute:

    $executor->execute($loader->getFixtures(), true);

## Sharing objects between fixtures

In case if fixture objects have relations to other fixtures, it is now possible
to easily add a reference to that object by name and later reference it to form
a relation. Here is an example fixtures for **Role** and **User** relation

    namespace MyDataFixtures;

    use Expio\Common\Neo4jDataFixtures\AbstractFixture;
    use HireVoice\Neo4j\EntityManager;

    class LoadUserRoleData extends AbstractFixture
    {
        public function load(ObjectManager $manager)
        {
            $adminRole = new Role();
            $adminRole->setName('admin');

            $anonymousRole = new Role;
            $anonymousRole->setName('anonymous');

            $manager->persist($adminRole);
            $manager->persist($anonymousRole);
            $manager->flush();

            // store reference to admin role for User relation to Role
            $this->addReference('admin-role', $adminRole);
        }
    }

And the **User** data loading fixture:

    namespace MyDataFixtures;

    use Expio\Common\Neo4jDataFixtures\AbstractFixture;
    use HireVoice\Neo4j\EntityManager;

    class LoadUserData extends AbstractFixture
    {
        public function load(ObjectManager $manager)
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');
            $user->setRole(
                $this->getReference('admin-role') // load the stored reference
            );

            $manager->persist($user);
            $manager->flush();

            // store reference of admin-user for other Fixtures
            $this->addReference('admin-user', $user);
        }
    }

## Fixture ordering
**Notice** that the fixture loading order is important! To handle it manually
implement one of the following interfaces:

### OrderedFixtureInterface

Set the order manually:

    namespace MyDataFixtures;

    use Expio\Common\Neo4jDataFixtures\AbstractFixture;
    use Expio\Common\Neo4jDataFixtures\OrderedFixtureInterface;
    use HireVoice\Neo4j\EntityManager;

    class MyFixture extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load(ObjectManager $manager)
        {}

        public function getOrder()
        {
            return 10; // number in which order to load fixtures
        }
    }

### DependentFixtureInterface

Provide an array of fixture class names:

    namespace MyDataFixtures;

    use Expio\Common\Neo4jDataFixtures\AbstractFixture;
    use Expio\Common\Neo4jDataFixtures\DependentFixtureInterface;
    use HireVoice\Neo4j\EntityManager;

    class MyFixture extends AbstractFixture implements DependentFixtureInterface
    {
        public function load(ObjectManager $manager)
        {}

        public function getDependencies()
        {
            return array('MyDataFixtures\MyOtherFixture'); // fixture classes fixture is dependent on
        }
    }

    class MyOtherFixture extends AbstractFixture
    {
        public function load(ObjectManager $manager)
        {}
    }

**Notice** the ordering is relevant to Loader class.

## Running the tests:

PHPUnit 3.5 or newer together with Mock_Object package is required.
To setup and run tests follow these steps:

- go to the root directory of data-fixtures
- run: **git submodule init**
- run: **git submodule update**
- copy the phpunit config **cp phpunit.xml.dist phpunit.xml**
- run: **phpunit**
