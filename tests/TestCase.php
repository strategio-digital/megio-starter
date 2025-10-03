<?php declare(strict_types=1);

namespace Tests;

use App\EntityManager;
use Megio\Debugger\JsonLogstashLogger;
use Megio\Helper\Path;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Bootstrap as TestBootstrap;

abstract class TestCase extends BaseTestCase
{
    private Container $container;

    private EntityManager $em;

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function getService(string $type): object
    {
        return $this->container->getByType($type);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();
        $this->em = $this->getService(EntityManager::class);

        // Clear database state before each test
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to keep tests isolated
        if ($this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();
        }

        parent::tearDown();
    }

    private function createContainer(): Container
    {
        return (new TestBootstrap())
            ->projectRootPath(__DIR__ . '/../')
            ->logger(new JsonLogstashLogger())
            ->configure(
                configPath: Path::configDir() . '/app.neon',
                startedAt: microtime(true),
            );
    }
}
