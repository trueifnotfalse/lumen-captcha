<?php

use Intervention\Image\ImageManager;

if (! function_exists('captcha')) {
    /**
     * @param string $config
     *
     * @throws Exception
     * @return array|ImageManager|mixed
     */
    function captcha(string $config = 'default')
    {
        return app('captcha')->create($config);
    }
}

if (! function_exists('captcha_src')) {
    /**
     * @param string $config
     *
     * @return string
     */
    function captcha_src(string $config = 'default'): string
    {
        return app('captcha')->src($config);
    }
}

if (! function_exists('captcha_img')) {

    /**
     * @param string $config
     *
     * @return string
     */
    function captcha_img(string $config = 'default'): string
    {
        return app('captcha')->img($config);
    }
}

if (! function_exists('captcha_check')) {
    /**
     * @param string $key
     * @param string $value
     * @param string $config
     *
     * @return bool
     */
    function captcha_check(string $key, string $value, string $config = 'default'): bool
    {
        return app('captcha')->check($key, $value, $config);
    }
}

if (! function_exists('captcha_value')) {
    /**
     * @param string $key
     *
     * @return mixed
     */
    function captcha_value(string $key)
    {
        return app('captcha')->getValue($key);
    }
}
