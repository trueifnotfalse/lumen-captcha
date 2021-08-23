<?php

namespace TrueIfNotFalse\LumenCaptcha;

use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Intervention\Image\Gd\Font;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

/**
 * Class Captcha
 *
 * @package TrueIfNotFalse\LumenCaptcha
 */
class Captcha
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var Str
     */
    protected $str;

    /**
     * @var ImageManager->canvas
     */
    protected $canvas;

    /**
     * @var Image
     */
    protected $image;

    /**
     * @var array
     */
    protected $backgrounds = [];

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * @var array
     */
    protected $fontColors = [];

    /**
     * @var int
     */
    protected $length = 5;

    /**
     * @var int
     */
    protected $width = 120;

    /**
     * @var int
     */
    protected $height = 36;

    /**
     * @var int
     */
    protected $angle = 15;

    /**
     * @var int
     */
    protected $lines = 3;

    /**
     * @var string
     */
    protected $characters;

    /**
     * @var int
     */
    protected $contrast = 0;

    /**
     * @var int
     */
    protected $quality = 90;

    /**
     * @var int
     */
    protected $sharpen = 0;

    /**
     * @var int
     */
    protected $blur = 0;

    /**
     * @var bool
     */
    protected $bgImage = true;

    /**
     * @var string
     */
    protected $bgColor = '#ffffff';

    /**
     * @var bool
     */
    protected $invert = false;

    /**
     * @var bool
     */
    protected $sensitive = false;

    /**
     * @var bool
     */
    protected $math = false;

    /**
     * @var int
     */
    protected $textLeftPadding = 4;

    /**
     * @var string
     */
    protected $fontsDirectory;

    /**
     * @var int
     */
    protected $expire = 600;

    /**
     * @var bool
     */
    protected $disposable = true;

    /**
     * Constructor
     *
     * @param Filesystem   $files
     * @param Repository   $config
     * @param ImageManager $imageManager
     * @param Hasher       $hasher
     * @param Str          $str
     *
     * @throws Exception
     * @internal param Validator $validator
     */
    public function __construct(
        Filesystem $files,
        Repository $config,
        ImageManager $imageManager,
        Hasher $hasher,
        Str $str
    ) {
        $this->files          = $files;
        $this->config         = $config;
        $this->imageManager   = $imageManager;
        $this->hasher         = $hasher;
        $this->str            = $str;
        $this->characters     = config('captcha.characters', [
            '1',
            '2',
            '3',
            '4',
            '6',
            '7',
            '8',
            '9',
        ]);
        $this->fontsDirectory = config('captcha.fontsDirectory', dirname(__DIR__) . '/assets/fonts');
    }

    /**
     * @param string $config
     *
     * @return void
     */
    protected function configure($config)
    {
        if ($this->config->has('captcha.' . $config)) {
            foreach ($this->config->get('captcha.' . $config) as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * Create captcha image
     *
     * @param string $config
     *
     * @throws Exception
     *
     * @return array
     */
    public function create(string $config = 'default'): array
    {
        $this->backgrounds = $this->files->files(__DIR__ . '/../assets/backgrounds');
        $this->fonts       = array_values($this->files->files($this->fontsDirectory));

        $this->configure($config);

        $generator = $this->generate();

        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );

        if ($this->bgImage) {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        } else {
            $this->image = $this->canvas;
        }

        if ($this->contrast != 0) {
            $this->image->contrast($this->contrast);
        }

        $this->text($generator['text']);

        $this->lines();

        if ($this->sharpen) {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert) {
            $this->image->invert();
        }
        if ($this->blur) {
            $this->image->blur($this->blur);
        }

        $this->putToCache($generator['key'], $generator['value']);

        return [
            'key' => $generator['key'],
            'img' => $this->image->encode('data-url')->encoded,
        ];
    }

    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background(): string
    {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }

    /**
     * Generate captcha text
     *
     * @throws Exception
     *
     * @return array
     */
    protected function generate(): array
    {
        $characters = is_string($this->characters) ? str_split($this->characters) : $this->characters;
        $bag        = [];

        if ($this->math) {
            $x     = random_int(10, 30);
            $y     = random_int(1, 9);
            $bag   = "$x + $y = ";
            $value = $x + $y;
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $char  = $characters[rand(0, count($characters) - 1)];
                $bag[] = $this->sensitive ? $char : $this->str->lower($char);
            }
            $value = implode('', $bag);
        }

        return [
            'value' => (string)$value,
            'text'  => $bag,
            'key'   => $this->hasher->make(microtime(true)),
        ];
    }

    /**
     * Writing captcha text
     *
     * @param array|string $text
     *
     * @return void
     */
    protected function text($text): void
    {
        $marginTop = $this->image->height() / $this->length;

        if (is_string($text)) {
            $text = str_split($text);
        }

        foreach ($text as $key => $char) {
            $marginLeft = $this->textLeftPadding + ($key * ($this->image->width() - $this->textLeftPadding) / $this->length);

            $this->image->text($char, $marginLeft, $marginTop, function ($font) {
                /* @var Font $font */
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });
        }
    }

    /**
     * Image fonts
     *
     * @return string
     */
    protected function font(): string
    {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }

    /**
     * Random font size
     *
     * @return int
     */
    protected function fontSize(): int
    {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return string
     */
    protected function fontColor(): string
    {
        if (! empty($this->fontColors)) {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        } else {
            $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return $color;
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle(): int
    {
        return rand((-1 * $this->angle), $this->angle);
    }

    /**
     * Random image lines
     *
     * @return Image|ImageManager
     */
    protected function lines()
    {
        for ($i = 0; $i <= $this->lines; $i++) {
            $this->image->line(
                rand(0, $this->image->width()) + $i * rand(0, $this->image->height()),
                rand(0, $this->image->height()),
                rand(0, $this->image->width()),
                rand(0, $this->image->height()),
                function ($draw) {
                    /* @var Font $draw */
                    $draw->color($this->fontColor());
                }
            );
        }

        return $this->image;
    }

    /**
     * Returns the md5 short version of the key for cache
     *
     * @param string $key
     *
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return 'captcha_' . md5($key);
    }

    /**
     * Captcha check
     *
     * @param string $key
     * @param string $value
     * @param string $config
     *
     * @return bool
     */
    public function check(string $key, string $value, string $config = 'default'): bool
    {
        $this->configure($config);

        $valueFromCache = $this->disposable ? $this->pullFromCache($key) : $this->getFromCache($key);
        if (empty($valueFromCache)) {
            return false;
        }

        if (! $this->sensitive) {
            $value = $this->str->lower($value);
        }

        return $valueFromCache === $value;
    }

    /**
     * Generate captcha image source
     *
     * @param string $config
     *
     * @return string
     */
    public function src(string $config = 'default'): string
    {
        return url('captcha/' . $config) . '?' . $this->str->random(8);
    }

    /**
     * Generate captcha image html tag
     *
     * @param string $config
     * @param array  $attrs
     * $attrs -> HTML attributes supplied to the image tag where key is the attribute and the value is the attribute value
     *
     * @return string
     */
    public function img(string $config = 'default', array $attrs = []): string
    {
        $attrs_str = '';
        foreach ($attrs as $attr => $value) {
            if ($attr == 'src') {
                //Neglect src attribute
                continue;
            }

            $attrs_str .= $attr . '="' . $value . '" ';
        }

        return new HtmlString('<img src="' . $this->src($config) . '" ' . trim($attrs_str) . '>');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->getFromCache($key);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getFromCache(string $key)
    {
        return Cache::get($this->getCacheKey($key));
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function pullFromCache(string $key)
    {
        return Cache::pull($this->getCacheKey($key));
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    protected function putToCache(string $key, string $value): void
    {
        Cache::put($this->getCacheKey($key), $value, $this->expire);
    }
}
