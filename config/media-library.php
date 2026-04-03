<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Library Configuration for Image Optimization
    |--------------------------------------------------------------------------
    */

    'disk_name' => env('MEDIA_DISK', 'public'),

    'max_file_size' => 1024 * 1024 * 100, // 100MB

    'queue_conversions_by_default' => false,

    'queue_name' => 'default',

    'should_sort_by_called_url_first' => false,

    'url_generator' => \Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

    'path_generator' => \Spatie\MediaLibrary\PathGenerator\DefaultPathGenerator::class,

    'atomic_uploads' => true,

    'moves_media_on_save' => true,

    'image_driver' => 'gd',

    'image_optimizers' => [
        \Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '--strip-all', // This strips out all profiles
            '--all-progressive', // This converts to progressive
        ],
        \Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force', // required
            '--skip-if-larger',
        ],
        \Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2',
            '-quiet',
        ],
        \Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--ps',
            '--convert-path-data=true', // convert some path data to short floating-point numbers
        ],
        \Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b',
            '-O3',
        ],
        \Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6', // for better compression but slower conversion
            '-segment 50 50 50 50 50', // useful for images with vocalized regions
            '-short',
        ],
    ],

    'responsive_images' => true,

    'conversion_file_namer' => \Spatie\MediaLibrary\Conversions\ConversionFileNamer::class,

    'media' => [
        'table_name' => 'media',
    ],

];
