<?php declare(strict_types=1);

namespace Tests;

use App\EntityManager;
use Exception;
use Megio\Debugger\JsonLogstashLogger;
use Megio\Helper\Path;
use Megio\Http\Kernel\App;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Bootstrap as TestBootstrap;

use function assert;
use function microtime;

abstract class TestCase extends BaseTestCase
{
    private ?Container $container = null;

    private ?EntityManager $em = null;

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function getService(string $type): object
    {
        assert($this->container !== null, 'Container must be initialized in setUp()');
        return $this->container->getByType($type);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();
        $this->em = $this->getService(EntityManager::class);

        // Initialize routing by creating kernel (loads routes into RouteCollection)
        $app = $this->getService(App::class);
        $app->createKernel();

        // Clear database state before each test
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to keep tests isolated
        if ($this->em !== null && $this->em->getConnection()->isTransactionActive() === true) {
            $this->em->rollback();
        }

        parent::tearDown();
    }

    /**
     * @throws Exception
     */
    private function createContainer(): Container
    {
        return new TestBootstrap()
            ->projectRootPath(__DIR__ . '/../')
            ->logger(new JsonLogstashLogger())
            ->configure(
                configPath: Path::configDir() . '/app.neon',
                startedAt: microtime(true),
            );
    }
}
