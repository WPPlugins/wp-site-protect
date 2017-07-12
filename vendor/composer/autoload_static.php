<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6699727b85f2bcc8401425bd153bd425
{
    public static $files = array (
        '74704c95e6224e3a13dba163dbbb87fa' => __DIR__ . '/..' . '/htmlburger/carbon-fields/carbon-fields.php',
        '1c3af1f7c867149c2eb8dfa733be2e98' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mowta\\SiteProtect\\' => 18,
        ),
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mowta\\SiteProtect\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6699727b85f2bcc8401425bd153bd425::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6699727b85f2bcc8401425bd153bd425::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
