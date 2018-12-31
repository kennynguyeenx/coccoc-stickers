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

use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Imagick\Encoder;

/**
 * Class Driver
 * @package Kennynguyeenx\ImageService\Imagick
 */
class Driver extends \Intervention\Image\Imagick\Driver
{
    /**
     * Driver constructor.
     * @param Decoder|null $decoder
     * @param Encoder|null $encoder
     */
    public function __construct(Decoder $decoder = null, Encoder $encoder = null)
    {
        if ( ! $this->coreAvailable()) {
            throw new NotSupportedException(
                "ImageMagick module not available with this PHP installation."
            );
        }

        $this->decoder = $decoder ? $decoder : new Decoder;
        $this->encoder = $encoder ? $encoder : new Encoder;
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function executeCommand($image, $name, $arguments)
    {
        $commandName = $this->getCommandClassName($name);
        $command = new $commandName($arguments);
        $command->execute($image);

        return $command;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getCommandClassName($name)
    {
        $name = mb_convert_case($name[0], MB_CASE_UPPER, 'utf-8') . mb_substr($name, 1, mb_strlen($name));

        $drivername = $this->getDriverName();
        $classnameOverrided = sprintf('\Coccoc\ImageService\%s\Commands\%sCommand', $drivername, ucfirst($name));
        $classnameLocal = sprintf('\Intervention\Image\%s\Commands\%sCommand', $drivername, ucfirst($name));
        $classnameGlobal = sprintf('\Intervention\Image\Commands\%sCommand', ucfirst($name));


        if (class_exists($classnameOverrided)) {
            return $classnameOverrided;
        } elseif (class_exists($classnameLocal)) {
            return $classnameLocal;
        } elseif (class_exists($classnameGlobal)) {
            return $classnameGlobal;
        }

        throw new NotSupportedException(
            "Command ({$name}) is not available for driver ({$drivername})."
        );
    }
}
