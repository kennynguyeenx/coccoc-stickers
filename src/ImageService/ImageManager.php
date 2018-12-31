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

use Intervention\Image\AbstractDriver;
use Intervention\Image\Exception\NotSupportedException;

/**
 * Class ImageManager
 * @package Kennynguyeenx\ImageService
 */
class ImageManager extends \Intervention\Image\ImageManager
{
    /**
     * @param mixed $data
     * @return \Intervention\Image\Image
     */
    public function make($data)
    {
        return $this->createDriver()->init($data);
    }

    /**
     * @param int $width
     * @param int $height
     * @param null $background
     * @return \Intervention\Image\Image
     */
    public function canvas($width, $height, $background = null)
    {
        return $this->createDriver()->newImage($width, $height, $background);
    }

    /**
     * @return AbstractDriver
     */
    private function createDriver()
    {
        if (is_string($this->config['driver'])) {
            $driverName = ucfirst($this->config['driver']);

            if ($driverName == 'Imagick') {
                $driverClass = 'Kennynguyeenx\ImageService\Imagick\Driver';
            } else {
                $driverClass = sprintf('Intervention\\Image\\%s\\Driver', $driverName);
            }


            if (class_exists($driverClass)) {
                return new $driverClass;
            }

            throw new NotSupportedException(
                "Driver ({$driverName}) could not be instantiated."
            );
        }

        if ($this->config['driver'] instanceof AbstractDriver) {
            return $this->config['driver'];
        }

        throw new NotSupportedException(
            "Unknown driver type."
        );
    }
}
