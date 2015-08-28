# Laravel CDN Assets Manager

[![Total Downloads](https://poser.pugx.org/vinelab/cdn/downloads)](https://packagist.org/packages/vinelab/cdn) 
[![Latest Stable Version](https://poser.pugx.org/vinelab/cdn/v/stable)](https://packagist.org/packages/vinelab/cdn) 
[![Latest Unstable Version](https://poser.pugx.org/vinelab/cdn/v/unstable)](https://packagist.org/packages/vinelab/cdn) 
[![Build Status](https://travis-ci.org/Vinelab/cdn.svg)](https://travis-ci.org/Vinelab/cdn)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Vinelab/cdn/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Vinelab/cdn/?branch=master)
[![License](https://poser.pugx.org/vinelab/cdn/license)](https://packagist.org/packages/vinelab/cdn)


##### Content Delivery Network Package for Laravel

This package gives Laravel developers the ability to use their assets, stored on a Content Delivery Network (CDN), in their views. The package allows for assets to be pushed to a CDN via a single artisan command.
The package is intelligent enough to allow developers to 'switch off' the CDN so that assets can be loaded into views locally, which helps for testing and development purposes.

#### Laravel Support
- For Laravel 5.1 use the latest release (`master`).
- For Laravel 4.2 use the release `v1.0.1` (https://github.com/Vinelab/cdn/releases/tag/v1.0.1)

## Highlights

- Connect to an Amazon Web Services (AWS) S3 bucket and load assets via AWS CloudFront
- Simple Artisan commands to upload your assets to a CDN and remove them
- Simple Facade to access CDN assets in your views
- Ability to work alongside Laravel Elixir Versioning
- Switch between loading your assets via a CDN and locally



## Installation

#### Via Composer

Require `vinelab/cdn` in your project:

```bash 
composer require vinelab/cdn:*
```

#### Register the service provider

Since this is a Laravel package we need to register the service provider. You can add the service provider to `config/app.php`:

```php
'providers' => array(
     //...
     Vinelab\Cdn\CdnServiceProvider::class,
),
```

## Configuration

##### Environment variables

If you're using AWS, set the Credentials in the `.env` file using the following two keys:

*Note: you must have an `.env` file at the project root, to hold your sensitive information.*

```bash
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

##### Configuration file

The configuration file can be publised by running the following command:

```bash
php artisan vendor:publish vinelab/cdn
```

This will create the file at `config/cdn.php`


##### Default storage provider

Currently only AWS S3 is supported for storing your assets remotely.

```php
'default' => 'AwsS3',
```

##### CDN provider configuration

The structure of your asset storage on S3 can be configured by modifying the following section of the `config/cdn.php` file:

```php
'aws' => [

    's3' => [
    
        'version'   => 'latest',
        'region'    => '',

        'buckets' => [
            'my-backup-bucket' => '*',
        ]
    ]
],
```

###### Multiple Buckets on AWS S3

You can also specify multiple buckets:

```php
'buckets' => [

    'my-default-bucket' => '*',
    
    // 'js-bucket'      => ['public/js'],
    // 'css-bucket'     => ['public/css'],
    // ...
]

```

#### Files & Directories

###### Files to include:

When uploading to S3 via the Artisan command (see below), you can specify which directories, file extensions and patterns you'd like to be uploaded:

```php
'include'    => [
    'directories'   => ['public/dist'],
    'extensions'    => ['.js', '.css', '.yxz'],
    'patterns'      => ['**/*.coffee'],
],
```

###### Files to exclude:

You can also specifiy what you'd like to be excluded:

```php
'exclude'    => [
    'directories'   => ['public/uploads'],
    'files'         => [''],
    'extensions'    => ['.TODO', '.txt'],
    'patterns'      => ['src/*', '.idea/*'],
    'hidden'        => true, // ignore hidden files
],
```

##### CDN URL

The URL for your CDN can be set in the following location:

```php
'url' => 'https://s3.amazonaws.com',
```

##### Loading assets

There may be occasions when you'd like to load your assets LOCALLY for testing or development purposes. To do this, just set the `bypass` option to `true`:

```php
'bypass' => true,
```

##### CloudFront support

If you use AWS CloudFront to serve your S3 assets over a CDN then you can modify the following section of the `config\cdn.php` file:

```php
'cloudront'    => [
    'use'     => false,
    'cdn_url' => ''
],
```


##### Other configurations

You can also specify additional data for S3:

```php
'acl'           => 'public-read',
'metadata'      => [ ],
'expires'       => gmdate("D, d M Y H:i:s T", strtotime("+5 years")),
'cache-control' => 'max-age=2628000',
```

You can always refer to the AWS S3 Documentation for more details: [aws-sdk-php](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/)

## Usage

#### Push/upload assets

Assets can be pushed to CDN via the following command:
```bash
php artisan cdn:push
```
#### Empty/remove assets

Assets on a CDN can also be emptied via the following command:
```bash
php artisan cdn:empty
```

#### Load assets in views

To load assets from the CDN in your views, you can call the `Cdn::asset()` function.

*Note: the `asset` works the same as the Laravel `Url::asset()` method and will look for assets in the `public/` directory:*

```blade
{{ Cdn::asset('assets/js/main.js') }}     // Output: https://js-bucket.s3.amazonaws.com/public/assets/js/main.js

{{ Cdn::asset('assets/css/style.css') }}  // Output: https://css-bucket.s3.amazonaws.com/public/assets/css/style.css
```

To use a file from outside the `public/` directory, anywhere in the `app/` directory, you can use the `Cdn::path()` function:

```blade
{{ Cdn::path('private/file.txt') }}       // Output: https://css-bucket.s3.amazonaws.com/private/something/file.txt
```

If you're using Laravel Elixir's Versioning function in your views, you can use it alongside the `Cdn::asset()` method:

```blade
{{ Cdn::asset(elixir('css/all.css')) }}   // Output: https://css-bucket.s3.amazonaws.com/public/build/css/all-1a73a6e76f.css
```

## Tests

To run the tests, run the following command from the project folder.

```bash
$ ./vendor/bin/phpunit
```

## Support

[Via Github](https://github.com/Vinelab/cdn/issues)


## Contributing

Please see [CONTRIBUTING](https://github.com/Vinelab/cdn/blob/master/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mahmoud@vinelab.com instead of using the issue tracker.

## Credits

- [Mahmoud Zalt](https://github.com/Mahmoudz)
- [All Contributors](../../contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/Vinelab/cdn/blob/master/LICENSE) for more information.


