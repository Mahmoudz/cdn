<?php

namespace Vinelab\Cdn\Tests;

use Illuminate\Config\Repository;
use Mockery as M;
use Vinelab\Cdn\CdnHelper;

/**
 * Class CdnHelperTest.
 * @category Test
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */
class CdnHelperTest extends \PHPUnit_Framework_TestCase
{
    const CDN_BASE_DIR = 'base';

    /**
     * @dataProvider dpGetCdnFilePath
     * @param string $localPath
     * @param string $cdnPath
     */
    public function testGetCdnFilePath($localPath, $cdnPath)
    {
        $helper = new CdnHelper(
            new Repository([
                'cdn' => $this->getCdnConfig()
            ])
        );

        $this->assertEquals(
            $cdnPath,
            $helper->getCdnFilePath($localPath)
        );
    }

    /**
     * @return array
     */
    public function dpGetCdnFilePath()
    {
        return [
            [
                'public/image.png',
                self::CDN_BASE_DIR.'/public/image.png',
            ],
            [
                '/public/image.png',
                self::CDN_BASE_DIR.'/public/image.png',
            ],
            [
                'image.png',
                self::CDN_BASE_DIR.'/image.png',
            ]
        ];
    }

    /**
     * @return array
     */
    private function getCdnConfig()
    {
        return [
            'bypass' => false,
            'default' => 'AwsS3',
            'url' => 'https://s3.amazonaws.com',
            'cdn_base_dir' => self::CDN_BASE_DIR,
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region' => 'us-standard',
                        'version' => 'latest',
                        'http' => null,
                        'buckets' => [
                            'my-bucket-name' => '*',
                        ],
                        'acl' => 'public-read',
                        'cloudfront' => [
                            'use' => false,
                            'cdn_url' => '',
                        ],
                        'metadata' => [],

                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),

                        'cache-control' => 'max-age=2628000',
                    ],
                ],
            ],
            'include' => [
                'directories' => [__DIR__],
                'extensions' => [],
                'patterns' => [],
            ],
            'exclude' => [
                'directories' => [],
                'files' => [],
                'extensions' => [],
                'patterns' => [],
                'hidden' => true,
            ],
        ];
    }
}
