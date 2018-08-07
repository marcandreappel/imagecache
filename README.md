# ImageCache
Fast image handling and caching.

```php
// The absolute path to the http image folder
$adapter = new \MarcAndreAppel\ImageCache\Adapter\Local('/absolute/path/to/base_folder/images');

// The browser accessible image path (https://domain.test/images/image.jpg
$sourceFile = 'image.jpg'

$imageCache = new \MarcAndreAppel\ImageCache\ImageCache($adapter, $sourceFile);

// Create a scaled and cropped thumbnail and return the public thumbnail path
$thumbnail = $imageCache->thumbnail(50, 50)->image;
```

## Properties

 - `public` ⇢ Returns the public accessible cached image (Alias: `publicImage`)  
 - `absolute` ⇢ Absolute path to the cached image (Alias: `absoluteImage`)
 - `publicPath` ⇢ Path to the cache folder only  
 - `absolutePath` ⇢ Absolute path to the cache folder

## Methods

### Cache path naming override
```php
$imageCache->method('replacement')->thumbnail(50, 50)->publicPath;
``` 
Returns '/replacement' instead of '/thumbnail/50/50'.
