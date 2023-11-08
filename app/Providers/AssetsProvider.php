<?php

declare(strict_types=1);

namespace App\Providers;

use Phalcon\Assets\Manager;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;

class AssetsProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    public const VERSION = "1.0.0";

    /**
     * @param  DiInterface  $di
     * @return void
     */
    public function register(DiInterface $di): void
    {
        $di->setShared('assets', function () {
            $js = [
                '/bundle.js'
            ];
            $css = [
                '/bundle.css'
            ];
            $assetManager = new Manager(new TagFactory(new Escaper()));
            foreach ($css as $asset) {
                $isLocal = (!str_starts_with($asset, 'http'));
                $assetManager->collection('css')
                    ->addCss(
                        $asset,
                        $isLocal,
                        false,
                        [],
                        $isLocal ? self::VERSION : null
                    );
            }
            foreach ($js as $asset) {
                $isLocal = (!str_starts_with($asset, 'http'));
                $assetManager->collection('js')
                    ->addJs(
                        $asset,
                        $isLocal,
                        false,
                        [],
                        $isLocal ? self::VERSION : null
                    );
            }

            return $assetManager;
        });
    }
}
