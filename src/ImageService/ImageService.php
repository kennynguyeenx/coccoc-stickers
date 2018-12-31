<?php
/**
 * This file is part of kennynguyeenx/image-service.
 *
 * (c) Kenny Nguyen <kennynguyeenx@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kennynguyeenx\ImageService;


use Imagick;

/**
 * Class ImageManager
 * @package Kennynguyeenx\ImageService
 */
class ImageService
{
    /**
     * @param $path
     * @return string
     */
    public function getMIME($path)
    {
        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'gif':
                return 'image/gif';
            case 'jpg':
                return 'image/jpg';
            case 'png':
                return 'image/png';
            case 'webp':
                return 'image/webp';
            default:
                return 'image/jpg';
        }
    }

    /**
     * @param $imageUrl
     * @param $options
     * @param $outputImagePath
     * @param $outputFormat
     * @return mixed
     * @throws ImageServiceException
     * @throws \ImagickException
     */
    protected function manipulateImage($imageUrl, $options, $outputImagePath, $outputFormat)
    {
        $sourceImgContent = @file_get_contents($imageUrl, false);

        if (!$sourceImgContent) {
            throw new ImageServiceException('Source image is not found');
        }

        $width = 0; $height = 0; $background = null;
        if (!empty($options['width']) && is_numeric($options['width'])) {
            $width = intval($options['width']);
        }

        if (!empty($options['height']) && is_numeric($options['height'])) {
            $width = intval($options['height']);
        }

        if (!empty($options['background'])
            && preg_match('/^([a-f0-9]{1,2})([a-f0-9]{1,2})([a-f0-9]{1,2})$/i', $options['background'])
        ) {
            $background = '#' . $options['background'];
        }

        $manager = new ImageManager(['driver' => 'imagick']);

        $image = $manager->make($sourceImgContent);
        if ($width || $height) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        };

        if ($width && $height) {
            $image->resizeCanvas($width, $height, 'center', false, $background);
        };

        $pathInfo = pathinfo($outputImagePath);
        if (!is_dir($pathInfo['dirname'])) {
            mkdir($pathInfo['dirname'], 0775, true);
        }

        $image->save($outputImagePath);
        $mime = $image->mime();
        if ($outputFormat == 'webp') {
            $tmpImagePath = $outputImagePath;
            $outputImagePath = $outputImagePath . '.webp';
            if ($pathInfo['extension'] == 'gif' && file_exists('/usr/bin/gif2webp')) {
                exec(sprintf(
                    '%s %s -o %s',
                    '/usr/bin/gif2webp',
                    escapeshellarg($tmpImagePath),
                    escapeshellarg($outputImagePath)
                ));
                $mime = 'image/webp';
            } elseif (in_array($pathInfo['extension'], ['png', 'jpg', 'jpeg']) && file_exists('/usr/bin/cwebp')) {
                exec(sprintf(
                    '%s -lossless %s -o %s',
                    '/usr/bin/cwebp',
                    escapeshellarg($tmpImagePath),
                    escapeshellarg($outputImagePath)
                ));
                $mime = 'image/webp';
            }
        } elseif ($outputFormat == 'gif') {
            $tmpImagePath = $outputImagePath;
            $outputImagePath = $outputImagePath . '.gif';
            $this->convertPngToGif($tmpImagePath, $outputImagePath);
            $mime = 'image/gif';
        }

        return $mime;
    }

    /**
     * @param string $sourcePath
     * @param string $desPath
     * @throws \ImagickException
     */
    protected function convertPngToGif(string $sourcePath, string $desPath)
    {
        $imagick = new Imagick($sourcePath);
        $imagick->setImageFormat('gif');
        $imagick->writeImages($desPath, false);
        $imagick->clear();
        $imagick->destroy();
    }
}
