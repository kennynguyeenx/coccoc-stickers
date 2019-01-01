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

- Resize image:

```php
use Kennynguyeenx\ImageService\ImageService;

try {
    $imageService = new ImageService();
    $imageService->manipulateImage($imageUrl, $options, $outputImagePath, $outputFormat);
} catch (Exception $exception) {
    exit($exception->getMessage());
}
```
- $options should consist these key: width (width of new image), height (height of new image), background (background color of new image if using canvas)

- I created a file to run in console to manipulate images as an example of using this class

- It's located at bin directory with name manipulate_image.php

- You can follow these steps to resize and convert image to the other type:

1. Save a image under the folder that you want. For me, i save it under images/source folder as example_image.png

![Background Image URL](README-assets/example_image.png)

2. This image have size is 300x300. We will try to resize it to 200x200 and convert it from png to gif. We can run this command:

```
$ php ./bin/manipulate_image.php ./images/source/example_image.png 200x200_ ./images/destination/updated_image.png gif
```

3. This is the result:

![An example stickerset](README-assets/updated_image.png.gif)
 
