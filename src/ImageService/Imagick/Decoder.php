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

namespace Kennynguyeenx\ImageService\Imagick;

use Intervention\Image\Image;

/**
 * Class Decoder
 * @package Kennynguyeenx\ImageService\Imagick
 */
class Decoder extends \Intervention\Image\Imagick\Decoder
{
    /**
     * @param \Imagick $object
     * @return Image
     */
    public function initFromImagick(\Imagick $object)
    {
        // Allow animation GIF
        //$object = $this->removeAnimation($object);

        // reset image orientation
        $object->setImageOrientation(\Imagick::ORIENTATION_UNDEFINED);

        return new Image(new Driver, $object);
    }
}
