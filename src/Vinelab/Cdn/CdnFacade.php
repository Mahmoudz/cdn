<?php

namespace Vinelab\Cdn;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\HtmlString;
use Vinelab\Cdn\Contracts\CdnFacadeInterface;
use Vinelab\Cdn\Contracts\CdnHelperInterface;
use Vinelab\Cdn\Contracts\ProviderFactoryInterface;
use Vinelab\Cdn\Exceptions\EmptyPathException;
use Vinelab\Cdn\Validators\CdnFacadeValidator;

/**
 * Class CdnFacade.
 *
 * @category
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class CdnFacade implements CdnFacadeInterface
{
    /**
     * @var array
     */
    protected $configurations;

    /**
     * @var \Vinelab\Cdn\Contracts\ProviderFactoryInterface
     */
    protected $provider_factory;

    /**
     * instance of the default provider object.
     *
     * @var \Vinelab\Cdn\Providers\Contracts\ProviderInterface
     */
    protected $provider;

    /**
     * @var \Vinelab\Cdn\Contracts\CdnHelperInterface
     */
    protected $helper;

    /**
     * @var \Vinelab\Cdn\Validators\CdnFacadeValidator
     */
    protected $cdn_facade_validator;

    /**
     * Calls the provider initializer.
     *
     * @param \Vinelab\Cdn\Contracts\ProviderFactoryInterface $provider_factory
     * @param \Vinelab\Cdn\Contracts\CdnHelperInterface       $helper
     * @param \Vinelab\Cdn\Validators\CdnFacadeValidator      $cdn_facade_validator
     */
    public function __construct(
        ProviderFactoryInterface $provider_factory,
        CdnHelperInterface $helper,
        CdnFacadeValidator $cdn_facade_validator
    ) {
        $this->provider_factory = $provider_factory;
        $this->helper = $helper;
        $this->cdn_facade_validator = $cdn_facade_validator;

        $this->init();
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::asset('')}} to convert the path into
     * it's CDN url.
     *
     * @param $path
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException
     */
    public function asset($path)
    {
        return $this->generateUrl($path);
    }

	/**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::elixir('')}} to convert the elixir generated file path into
     * it's CDN url.
     *
     * @param $path
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException, \InvalidArgumentException
     */
	public function elixir($path)
    {
        static $manifest = null;
        if (is_null($manifest)) {
            $manifest = json_decode(file_get_contents(public_path('build/rev-manifest.json')), true);
        }
        if (isset($manifest[$path])) {
            return $this->generateUrl('build/' . $manifest[$path]);
        }
        throw new \InvalidArgumentException("File {$path} not defined in asset manifest.");
    }

	/**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::mix('')}} to convert the Laravel 5.4 webpack mix
     * generated file path into it's CDN url.
     *
     * @param $path
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException, \InvalidArgumentException
     */
	public function mix($path)
    {
        static $manifests = [];

        if (! starts_with($path, '/')) {
            $path = "/{$path}";
        }

        $manifestPath = public_path('/mix-manifest.json');

        if (! isset($manifests[$manifestPath])) {
            if (! file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (! isset($manifest[$path])) {
            throw new \InvalidArgumentException(
                "Unable to locate Mix file: {$path}. Please check your ".
                'webpack.mix.js output paths and try again.'
            );
        }
        return $this->generateUrl($manifest[$path]);
    }

    /**
     * this function will be called from the 'views' using the
     * 'Cdn' facade {{Cdn::path('')}} to convert the path into
     * it's CDN url.
     *
     * @param $path
     *
     * @return mixed
     *
     * @throws Exceptions\EmptyPathException
     */
    public function path($path)
    {
        return $this->generateUrl($path);
    }

    /**
     * check if package is surpassed or not then
     * prepare the path before generating the url.
     *
     * @param        $path
     *
     * @return mixed
     */
    private function generateUrl($path)
    {
        // if the package is surpassed, then return the same $path
        // to load the asset from the localhost
        if (isset($this->configurations['bypass']) && $this->configurations['bypass']) {
            return new HtmlString(asset($path));
        }

        if (!isset($path)) {
            throw new EmptyPathException('Path does not exist.');
        }

        // Add version number
        //$path = str_replace(
        //    "build",
        //    $this->configurations['providers']['aws']['s3']['version'],
        //    $path
        //);

        // remove slashes from begging and ending of the path
        // and append directories if needed
        $clean_path = $this->helper->cleanPath($path);

        // call the provider specific url generator
        return $this->provider->urlGenerator($clean_path);
    }

    /**
     * Read the configuration file and pass it to the provider factory
     * to return an object of the default provider specified in the
     * config file.
     */
    private function init()
    {
        // return the configurations from the config file
        $this->configurations = $this->helper->getConfigurations();

        // return an instance of the corresponding Provider concrete according to the configuration
        $this->provider = $this->provider_factory->create($this->configurations);
    }
}
