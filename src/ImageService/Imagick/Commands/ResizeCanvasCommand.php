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

namespace Kennynguyeenx\ImageService\Imagick\Commands;

use Intervention\Image\Commands\AbstractCommand;

/**
 * Class ResizeCanvasCommand
 * @package Kennynguyeenx\ImageService\Imagick\Commands
 */
class ResizeCanvasCommand extends AbstractCommand
{
    /**
     * Resizes image boundaries
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     * @throws \ImagickException
     */
    public function execute($image)
    {
        $width = $this->argument(0)->type('digit')->required()->value();
        $height = $this->argument(1)->type('digit')->required()->value();
        $anchor = $this->argument(2)->value('center');
        $relative = $this->argument(3)->type('boolean')->value(false);
        $bgColor = $this->argument(4)->value();

        $originalWidth = $image->getWidth();
        $originalHeight = $image->getHeight();

        // check of only width or height is set
        $width = is_null($width) ? $originalWidth : intval($width);
        $height = is_null($height) ? $originalHeight : intval($height);

        // check on relative width/height
        if ($relative) {
            $width = $originalWidth + $width;
            $height = $originalHeight + $height;
        }

        // check for negative width/height
        $width = ($width <= 0) ? $width + $originalWidth : $width;
        $height = ($height <= 0) ? $height + $originalHeight : $height;

        // create new canvas
        $canvas = $image->getDriver()->newImage($width, $height, $bgColor);
        list($srcX, $srcY, $srcWidth, $srcHeight, $dstX, $dstY) = $this->createParametersForImage(
            $canvas,
            $image,
            $anchor,
            $width,
            $height,
            $originalWidth,
            $originalHeight
        );


        if (strtolower($image->mime()) == 'image/gif' || strtolower($image->extension) == 'gif') {
            $imagick = $this->createGifImage(
                $canvas,
                $image,
                $srcWidth,
                $srcHeight,
                $srcX,
                $srcY,
                $dstX,
                $dstY,
                $width,
                $height,
                $bgColor
            );
        } else {
            $imagick = $this->createNonGifImage(
                $canvas,
                $image,
                $srcWidth,
                $srcHeight,
                $srcX,
                $srcY,
                $dstX,
                $dstY,
                $bgColor
            );
        }

        // set new core to canvas
        $image->setCore($imagick);

        return true;
    }

    /**
     * @param $canvas
     * @param $image
     * @param $anchor
     * @param $width
     * @param $height
     * @param $originalWidth
     * @param $originalHeight
     * @return array
     */
    protected function createParametersForImage(
        $canvas,
        $image,
        $anchor,
        $width,
        $height,
        $originalWidth,
        $originalHeight
    ) {
        // set copy position
        $canvasSize = $canvas->getSize()->align($anchor);
        $imageSize = $image->getSize()->align($anchor);
        $canvasPos = $imageSize->relativePosition($canvasSize);
        $imagePos = $canvasSize->relativePosition($imageSize);

        if ($width <= $originalWidth) {
            $dstX = 0;
            $srcX = $canvasPos->x;
            $srcWidth = $canvasSize->width;
        } else {
            $dstX = $imagePos->x;
            $srcX = 0;
            $srcWidth = $originalWidth;
        }

        if ($height <= $originalHeight) {
            $dstY = 0;
            $srcY = $canvasPos->y;
            $srcHeight = $canvasSize->height;
        } else {
            $dstY = $imagePos->y;
            $srcY = 0;
            $srcHeight = $originalHeight;
        }

        return [$srcX, $srcY, $srcWidth, $srcHeight, $dstX, $dstY];
    }

    /**
     * @param $canvas
     * @param $image
     * @param $srcWidth
     * @param $srcHeight
     * @param $srcX
     * @param $srcY
     * @param $dstX
     * @param $dstY
     * @return \Imagick
     * @throws \ImagickException
     */
    protected function createGifImage(
        $canvas,
        $image,
        $srcWidth,
        $srcHeight,
        $srcX,
        $srcY,
        $dstX,
        $dstY
    ) {
        $oldGif = $image->getCore();
        $animation = new \Imagick();
        $animation->setFormat("GIF");

        $oldGif = $oldGif->coalesceImages();
        do {
            $newImagick = $canvas->getCore();
            $newImagick->setFormat("GIF");

            // copy image into new canvas
            $oldGif->cropImage($srcWidth, $srcHeight, $srcX, $srcY);
            $newImagick->compositeImage($oldGif, \Imagick::COMPOSITE_DEFAULT, $dstX, $dstY, \Imagick::CHANNEL_DEFAULT);
            $animation->addImage($newImagick->getImage());
            $animation->setImageDelay($oldGif->getImageDelay());
            $animation->setImageDispose(2);
        } while ($oldGif->nextImage());

        return $animation;
    }

    /**
     * @param $canvas
     * @param $image
     * @param $srcWidth
     * @param $srcHeight
     * @param $srcX
     * @param $srcY
     * @param $dstX
     * @param $dstY
     * @param $bgColor
     * @return \Imagick
     */
    protected function createNonGifImage(
        $canvas,
        $image,
        $srcWidth,
        $srcHeight,
        $srcX,
        $srcY,
        $dstX,
        $dstY,
        $bgColor
    ) {
        if (!empty($bgColor)) {
            $image->getCore()->setImageBackgroundColor(new \ImagickPixel($bgColor));
            $image->getCore()->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $image->getCore()->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        } else {
            // make image area transparent to keep transparency
            // even if background-color is set
            $rect = new \ImagickDraw;
            $fill = $canvas->pickColor(0, 0, 'hex');
            $fill = $fill == '#ff0000' ? '#00ff00' : '#ff0000';
            $rect->setFillColor($fill);
            $rect->rectangle($dstX, $dstY, $dstX + $srcWidth - 1, $dstY + $srcHeight - 1);
            $canvas->getCore()->drawImage($rect);
            $canvas->getCore()->transparentPaintImage($fill, 0, 0, false);
            $canvas->getCore()->setImageColorspace($image->getCore()->getImageColorspace());
        }

        // copy image into new canvas
        $image->getCore()->cropImage($srcWidth, $srcHeight, $srcX, $srcY);
        $canvas->getCore()->compositeImage($image->getCore(), \Imagick::COMPOSITE_DEFAULT, $dstX, $dstY);
        $canvas->getCore()->setImagePage(0,0,0,0);
        $imagick = $image->getCore();
        return $imagick;
    }
}
