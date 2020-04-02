<?php
namespace App\Utils;

use \Imagick;


class ImageService
{
    private $rootDir;

    function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    function createThumbnail($pathname, $thumbPathname, $width = null, $height = null)
    {
        $thumb = new Imagick();
        $thumb->readImage($pathname);
        $thumb->resizeImage($width,$height,Imagick::FILTER_CATROM,1, true);
        $thumb->writeImage($thumbPathname);
        $thumb->clear();
        $thumb->destroy();
        return true;
    }

    function resizeImage($pathname, $width, $height){
        $image = new Imagick();
        $image->readImage($pathname);
        $image->resizeImage($width,$height,Imagick::FILTER_CATROM,1, true);
        $image->writeImage($pathname);
        $image->clear();
        $image->destroy();
    }
}

?>


