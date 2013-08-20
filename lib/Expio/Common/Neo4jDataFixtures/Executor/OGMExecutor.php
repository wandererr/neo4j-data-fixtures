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

namespace Expio\Common\Neo4jDataFixtures\Executor;

use HireVoice\Neo4j\EntityManager;
use Expio\Common\Neo4jDataFixtures\Purger\OGMPurger;
use Expio\Common\Neo4jDataFixtures\Event\Listener\OGMReferenceListener;
use Expio\Common\Neo4jDataFixtures\ReferenceRepository;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class OGMExecutor extends AbstractExecutor
{
    /**
     * Construct new fixtures loader instance.
     *
     * @param EntityManager $em EntityManager instance used for persistence.
     */
    public function __construct(EntityManager $em, OGMPurger $purger = null)
    {
        $this->em = $em;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setEntityManager($em);
        }
        parent::__construct($em);
    }

    /**
     * Retrieve the EntityManager instance this executor instance is using.
     *
     * @return \HireVoice\Neo4j\EntityManager
     */
    public function getObjectManager()
    {
        return $this->em;
    }

    /** @inheritDoc */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        $executor = $this;
        if ($append === false) {
            $executor->purge();
        }
        foreach ($fixtures as $fixture) {
            $executor->load($em, $fixture);
        }
    }
}
