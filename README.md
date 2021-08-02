# Captcha for Laravel Lumen 8

## Preview
![Preview](https://image.ibb.co/kZxMLm/image.png)

- [Captcha for Laravel Lumen 8](#captcha-for-laravel-lumen-8)
  * [Preview](#preview)
  * [Installation](#installation)
  * [Usage](#usage)
  * [Configuration](#configuration)
  * [Example Usage](#example-usage)
    + [Stateless Mode:](#stateless-mode-)
- [Return Image](#return-image)
- [Return URL](#return-url)
- [Return HTML](#return-html)
- [To use different configurations](#to-use-different-configurations)
  * [Links](#links)
  
## Installation

The Captcha Service Provider can be installed via [Composer](http://getcomposer.org) by requiring the
`trueifnotfalse/lumen-captcha` package.

Require this package with composer:
```
composer require trueifnotfalse/lumen-captcha
```
Update your packages with ```composer update``` or install with ```composer install```.

## Usage

To use the Captcha Service Provider, you must register the provider when bootstrapping your application. There are
essentially two ways to do this.

Add to `bootstrap/app.php` and register the Captcha Service Provider.

```php
    $app->register(TrueIfNotFalse\LumenCaptcha\CaptchaServiceProvider::class);
```

## Configuration

To use your own settings, create config file.

`config/captcha.php`

```php
return [
    'default'   => [
        'length'    => 5,
        'width'     => 120,
        'height'    => 36,
        'quality'   => 90,
        'math'      => true,  //Enable Math Captcha
        'expire'    => 60,    //Stateless/API captcha expiration
    ],
    // ...
];
```

and enable it in `bootstrap/app.php`
```php
    $app->configure('captcha');
```

### Stateless Mode:
You get key and img from this url
`http://localhost/captcha/default`
and verify the captcha using this method:
```php
    //key is the one that you got from json response
    $rules = ['captcha' => 'required|captcha:'. request('key') . ',math'];
```

# Return Image
```php
captcha();
```
or
```php
Captcha::create();
```


# Return URL
```php
captcha_src();
```
or
```
Captcha::src('default');
```

# Return HTML
```php
captcha_img();
```
or
```php
Captcha::img();
```

# To use different configurations
```php
captcha_img('flat');

Captcha::img('inverse');
```
etc.

Based on [Intervention Image](https://github.com/Intervention/image)


## Links
* [Intervention Image](https://github.com/Intervention/image)
* [License](http://www.opensource.org/licenses/mit-license.php)
* [Laravel Lumen website](http://lumen.laravel.com)
