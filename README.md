kennynguyeenx/image-service
=============

> Provide some functions to manipulate images

Features
--------

- Resize image
- Set background color for image
- Create bigger image with old image in the center
- Convert image to the other type
- Dependencies: imagemagick, webp
- PSR-4 compatible.
- Compatible with PHP >= PHP 7.

Installation
------------

- You can download image-service through https://github.com/kennynguyeenx/image-service.

- image-service requires the Multibyte String extension and the Imagick extension from PHP.
 
- Typically you can use the configure option `--enable-mbstring` while compiling PHP to have the Multibyte String extension. 
More information can be found in the [PHP documentation](http://php.net/manual/en/intro.mbstring.php).
 
- Imagick is a native php extension to create and modify images using the ImageMagick API.
More information can be found in the [PHP documentation](http://php.net/manual/en/intro.imagick.php).
Usage
-----

Resize image:

```php
use Kennynguyeenx\ImageService\ImageService;

$imageService = new ImageService();
$imageServie->manipulateImage();
```
