# ImageCache
Fast image handling and caching.

```php
$imageCache = new \MarcAndreAppel\ImageCache\ImageCache('/absolute/path/to/base_folder/images');

// Create a scaled and cropped thumbnail and return the public thumbnail path
$thumbnail = $imageCache->thumbnail(50, 50)->cachedImage;
```

## Methods (chainable)

### Cache path naming override
```php
$imageCache->method('replacement')->thumbnail(50, 50)->publicPath;
``` 
Returns */replacement* instead of */thumbnail/50/50*.

### Cache path prefixing
```php
$imageCache->prefix('mycache')->thumbnail(50, 50)->publicPath;
``` 
Adds */mycache* to the resulting path, eg: */mycache/thumbnail/50/50*.

### Allow enlarging of images
```php
$imageCache->enlarge(true)->scale(1200);
``` 
  