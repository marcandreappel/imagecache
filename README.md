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
Returns */.cache/replacement* instead of */.cache/thumbnail/50/50*.

### Cache path prefixing
```php
$imageCache->prefix('mycache')->thumbnail(50, 50)->publicPath;
``` 
Adds */.cache/mycache* to the resulting path, eg: */.cache/mycache/thumbnail/50/50*.

### Allow enlarging of images
```php
$imageCache->enlarge(true)->scale(1200);
``` 
Original size might be 400px ✕ 300px and hence becomes bigger.
  
### Create visible subfolders
```php
$imageCache->hidden(false)->scale(1200);
``` 
