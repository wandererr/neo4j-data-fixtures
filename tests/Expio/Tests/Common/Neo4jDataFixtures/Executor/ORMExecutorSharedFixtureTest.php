<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Expio\Tests\Common\Neo4jDataFixtures;

require_once __DIR__.'/../TestInit.php';

use Expio\Common\Neo4jDataFixtures\Executor\OGMExecutor;
use Expio\Common\Neo4jDataFixtures\Purger\OGMPurger;
use Doctrine\ORM\Proxy\Proxy;
use Expio\Tests\Common\Neo4jDataFixtures\TestEntity\Role;
use Expio\Tests\Common\Neo4jDataFixtures\TestEntity\User;

/**
 * Test referenced fixture execution
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class ORMExecutorSharedFixtureTest extends BaseTest
{
    const TEST_ENTITY_ROLE = 'Doctrine\Tests\Common\Neo4jDataFixtures\TestEntity\Role';
    const TEST_ENTITY_USER = 'Doctrine\Tests\Common\Neo4jDataFixtures\TestEntity\User';

    public function testFixtureExecution()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $purger = new OGMPurger();
        $executor = new OGMExecutor($em, $purger);

        $referenceRepository = $executor->getReferenceRepository();
        $fixture = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);

        $fixture->expects($this->once())
            ->method('setReferenceRepository')
            ->with($referenceRepository);

        $executor->execute(array($fixture), true);
    }

    public function testSharedFixtures()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $em = $this->getMockSqliteEntityManager();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $em->getClassMetadata(self::TEST_ENTITY_ROLE),
            $em->getClassMetadata(self::TEST_ENTITY_USER)
        ));

        $purger = new OGMPurger();
        $executor = new OGMExecutor($em, $purger);

        $userFixture = new TestFixtures\UserFixture;
        $roleFixture = new TestFixtures\RoleFixture;
        $executor->execute(array($roleFixture, $userFixture), true);

        $referenceRepository = $executor->getReferenceRepository();
        $references = $referenceRepository->getReferences();

        $this->assertEquals(2, count($references));
        $roleReference = $referenceRepository->getReference('admin-role');
        $this->assertTrue($roleReference instanceof Role);
        $this->assertEquals('admin', $roleReference->getName());

        $userReference = $referenceRepository->getReference('admin');
        $this->assertTrue($userReference instanceof User);
        $this->assertEquals('admin@example.com', $userReference->getEmail());
    }

    private function getMockFixture()
    {
        return $this->getMock('Doctrine\Common\Neo4jDataFixtures\SharedFixtureInterface');
    }
}
