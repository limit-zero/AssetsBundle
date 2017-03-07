# Limit0AssetsBundle
Implements image upload support for projects utilizing [limit0/assets](https://github.com/limit-zero/assets)

## Requirements
- You need an AWS account with access to an S3 bucket, or
- You need write access via your web server user to a path for local storage.

## Installation

Install the package via composer:
```
composer require limit0/assets-bundle
```

Include the bundle in your `AppKernel.php`:
```php
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Limit0\AssetsBundle\Limit0AssetsBundle(),
            // ...
```

## Configuration

Configure the bundle using your preferred asset storage provider. Full possible configuration is listed below:

```yml
limit0_assets:
    engine: aws_s3                              # Either `aws_s3` or `local_storage`
    http_prefix: //my-cdn.io/path-to-images/    # A URL prefix for your uploaded images. Can be relative.
    # The URL provided by the uploader will prefix the filename with this path, so they can be viewed.
    aws_s3:
        region: us-east-1                       # Default S3 storage region
        acl: public-read                        # Default ACL for uploaded files
        bucket: mybucket                        # Bucket to upload files to
    local_storage:
        path: /uploads/myproject                # Path on server to upload files to. Can be relative.
```

If using AWS S3, `bucket` is required. If using local storage, `path` is required.

### Routing
You will need to import this bundle's routing. To prevent any potential collision issues, be sure to load it any other application routes:
```yml

limit0_assets:
    resource: "@Limit0AssetsBundle/Resources/config/routing.yml"
    prefix:   /

app_bundle:
    resource: "@AppBundle/Resources/config/routing.yml"
# ...
```
