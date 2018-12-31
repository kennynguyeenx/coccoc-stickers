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
 * Class ResizeCommand
 * @package Kennynguyeenx\ImageService\Imagick\Commands
 */
class ResizeCommand extends AbstractCommand
{
    /**
     * Resizes image dimensions
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $width = $this->argument(0)->value();
        $height = $this->argument(1)->value();
        $constraints = $this->argument(2)->type('closure')->value();

        // resize box
        $resized = $image->getSize()->resize($width, $height, $constraints);

        // modify image
        $imagick = $image->getCore();
        if (strtolower($image->mime()) == 'image/gif' || strtolower($image->extension) == 'gif') {
            $imagick = $imagick->coalesceImages();
            do {
                $imagick->resizeImage($resized->getWidth(), $resized->getHeight(), \Imagick::FILTER_SINC, 1);
            } while ($imagick->nextImage());
            $image->setCore($imagick);
        } else {
            $imagick->scaleImage($resized->getWidth(), $resized->getHeight());
        }

        return true;
    }
}
