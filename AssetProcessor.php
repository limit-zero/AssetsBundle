<?php

namespace Limit0\AssetsBundle;

use As3\Modlr\Store\Store;
use Limit0\Assets\Asset;
use Limit0\Assets\AssetFactory;
use Limit0\Assets\AssetManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Loads information pertaining to the site.
 *
 * @author  Josh Worden <josh@limit0.io>
 */
class AssetProcessor
{
    private $assetManager;

    private $store;

    private $httpPrefix;

    public function __construct(Store $store, AssetManager $manager, $httpPrefix)
    {
        $this->store = $store;
        $this->assetManager = $manager;
        $this->httpPrefix = $httpPrefix;
    }

    /**
     * Uploads a Base64 encoded asset using the AssetManager.
     *
     * @param   string      $filename   The original filename.
     * @param   string      $data       The base64 data, in string format, e.g. 'data:image/png;base64,AAAFBfj42Pj4'.
     * @param   string|null $path       The target upload path.
     */
    public function processBase64Upload($filename, $data, $path = null)
    {
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $tmpFilename = sprintf('%s/%s', sys_get_temp_dir(), $filename);
        if (false === file_put_contents($tmpFilename, $data)) {
            // File write error. Handle.
            throw new \RuntimeException(sprintf('Unable to write file "%s" to disk.', $filename));
        }
        $asset    = AssetFactory::createFromPath($tmpFilename);
        $filename = $this->cleanFilename($asset);
        $this->assetManager->store($asset, $path, $filename);
        return sprintf('%s/%s/%s', $this->httpPrefix, $path, $filename);
    }

    /**
     * Uploads an asset using the AssetManager.
     *
     * @param   string          $path   The path to prefix the file with
     * @param   UploadedFile    $file   The file to process
     *
     * @throws  \RuntimeException
     * @return  string          The file's accessible URL
     */
    public function processUpload($path, UploadedFile $file)
    {
        $path       = sprintf('%s/%s/%s', date('Y'), date('m'), $path);
        $asset      = AssetFactory::createFromUploadedFile($file);
        $filename   = $this->cleanFilename($asset);

        $this->assetManager->store($asset, $path, $filename);
        return sprintf('%s/%s/%s', $this->httpPrefix, $path, $filename);
    }

    public function removeByUrl($url)
    {
        $identifier = ltrim(str_replace($this->httpPrefix, '', $url), '/');
        return $this->assetManager->remove($identifier);
    }

    private function cleanFilename(Asset $asset)
    {
        $extension  = $asset->getClientOriginalExtension();
        $filename   = $asset->getClientOriginalName();
        $filename   = preg_replace("/[^A-Za-z0-9\._]/", '_', $filename);
        $parts      = explode('.', $filename);
        $ext        = array_pop($parts);
        $parts[]    = uniqid();
        $parts[]    = $extension;
        $filename   = implode('.', $parts);
        $asset->setFileName($filename);
        return $filename;
    }
}
