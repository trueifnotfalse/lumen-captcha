<?php

namespace TrueIfNotFalse\LumenCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Laravel\Lumen\Routing\Router;

/**
 * Class CaptchaServiceProvider
 *
 * @package TrueIfNotFalse\LumenCaptcha
 */
class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration files
        $this->publishes([
            __DIR__ . '/../config/captcha.php' => app()->configPath('captcha.php'),
        ], 'config');

        $this->bootValidationTranslation();

        /* @var Router $router */
        $router = $this->app->router;

        $router->get('captcha[/{config}]', '\TrueIfNotFalse\LumenCaptcha\CaptchaController@get');

        /* @var Factory $validator */
        $validator = $this->app['validator'];

        // Validator extensions
        $validator->extend('captcha', function ($attribute, $value, $parameters) {
            return captcha_check($parameters[0], $value, $parameters[1] ?? 'default');
        }, trans('lumen-captcha::validation.captcha'));
    }

    /**
     * @return void
     */
    private function bootValidationTranslation(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'lumen-captcha');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configs
        $this->mergeConfigFrom(
            __DIR__ . '/../config/captcha.php',
            'captcha'
        );

        // Bind captcha
        $this->app->bind('captcha', function ($app) {
            return new Captcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Contracts\Config\Repository'],
                $app['Intervention\Image\ImageManager'],
                $app['Illuminate\Hashing\BcryptHasher'],
                $app['Illuminate\Support\Str']
            );
        });
    }
}
