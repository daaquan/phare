<?php

namespace Tests;

use Framework\Contracts\Foundation\Application;
use Framework\Contracts\Http\Kernel;
use Framework\Foundation\Http\RequestMethod;
use Framework\Support\Facades\Log;
use Framework\Testing\TestCase as BaseTestCase;
use Framework\Testing\TestResponse;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Http\Request;

class TestCase extends BaseTestCase
{
    private ?Application $app = null;

    public function post(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::POST, $data);
    }

    public function get(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::GET, $data);
    }

    public function put(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::PUT, $data);
    }

    public function delete(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::DELETE, $data);
    }

    public function patch(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::PATCH, $data);
    }

    public function options(string $uri, $data = []): TestResponse
    {
        return $this->request($uri, RequestMethod::OPTIONS, $data);
    }

    public function request(string $uri, RequestMethod $method, $data = []): TestResponse
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        $memory = memory_get_usage();

        $kernel = $this->createApplication()
            ->make(Kernel::class);

        $_SERVER['REQUEST_URI'] = config('app.url') . $uri;
        $_SERVER['REQUEST_METHOD'] = $method->name;

        $var = "_" . $method->name;
        $$var = $_REQUEST = $data;

        $response = $kernel->handle($request = new Request());
        Log::debug($request->getRawBody());

        $size = memory_get_usage() - $memory;
        $size = round($size / (1024 ** ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        Log::debug("$uri => $size");
        $peak = memory_get_peak_usage() - $memory;
        $peak = round($peak / (1024 ** ($i = floor(log($peak, 1024)))), 2) . ' ' . $unit[$i];
        Log::debug("$uri => $peak");

        return new TestResponse($response);
    }

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        if (!defined('APP_RUNNING_UNIT_TEST')) {
            define('APP_RUNNING_UNIT_TEST', true);
        }

        return require __DIR__ . '/../bootstrap/app.php';
    }

    protected function setUp(): void
    {
        $this->setUpApplication();
    }

    public function setUpApplication(): void
    {
        Di::reset();

        $this->app = $this->createApplication();
        $this->app->bootstrapWith([
            \Framework\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \Framework\Foundation\Bootstrap\LoadConfiguration::class,
            \Framework\Foundation\Bootstrap\RegisterFacades::class,
            \Framework\Foundation\Bootstrap\RegisterProviders::class,
        ]);

        Di::setDefault($this->app);
    }

    /**
     * Sets the Dependency Injector.
     *
     * @param DiInterface $di
     * @return $this
     * @see    Injectable::setDI
     */
    public function setDI(DiInterface $di)
    {
        $this->app = $di;

        return $this;
    }

    /**
     * Returns the internal Dependency Injector.
     *
     * @return DiInterface
     * @see    Injectable::getDI
     */
    public function getDI()
    {
        if (!$this->app instanceof DiInterface) {
            return Di::getDefault();
        }

        return $this->app;
    }
}