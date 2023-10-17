<?php

namespace Tests;

use Framework\Contracts\Foundation\Application;
use Framework\Foundation\Http\RequestMethod;
use Framework\Foundation\Micro;
use Framework\Testing\TestCase as BaseTestCase;
use Framework\Testing\TestResponse;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;

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
        $this->initializeServerVariables($uri, $method);
        $this->initializeRequestData($method, $data);

        /** @var \Framework\Contracts\Http\Kernel $kernel */
        $kernel = $this->app->make(\Framework\Contracts\Http\Kernel::class);

        $response = $kernel->handle(
            $request = $this->app->make('request')
        );
        $kernel->terminate($request, $response);

        return new TestResponse($response);
    }

    /**
     * サーバー変数を初期化する
     *
     * @param string $uri
     * @param RequestMethod $method
     */
    private function initializeServerVariables(string $uri, RequestMethod $method): void
    {
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = $_SERVER['HOST_NAME'] = $_SERVER['SERVER_ADDR']
            = parse_url(config('app.url', 'localhost'), PHP_URL_HOST);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method->name;
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        $_SERVER['REQUEST_TIME'] = (int)$_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * リクエストデータを初期化する
     *
     * @param RequestMethod $method
     * @param array $data
     */
    private function initializeRequestData(RequestMethod $method, array $data): void
    {
        global $_GET, $_POST, $_PUT, $_DELETE, $_PATCH, $_OPTIONS;

        switch ($method->name) {
            case 'GET':
                $_GET = $data;
                break;
            case 'POST':
                $_POST = $data;
                break;
            case 'PUT':
                $_PUT = $data;
                break;
            case 'DELETE':
                $_DELETE = $data;
                break;
            case 'PATCH':
                $_PATCH = $data;
                break;
            case 'OPTIONS':
                $_OPTIONS = $data;
                break;
        }

        $_REQUEST = $data;
    }

    /**
     * Creates the application.
     *
     * @return Micro
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
        if ($this->app !== null) {
            return;
        }

        Di::reset();

        $app = $this->createApplication();
        $app->bootstrapWith([
            \Framework\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \Framework\Foundation\Bootstrap\LoadConfiguration::class,
            \Framework\Foundation\Bootstrap\HandleExceptions::class,
            \Framework\Foundation\Bootstrap\RegisterProviders::class,
            \Framework\Foundation\Bootstrap\RegisterFacades::class,
        ]);

        Di::setDefault($this->app = $app);
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