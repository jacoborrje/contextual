<?php
namespace App\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class FileUploader
{
    private $rootDir;
    private $targetDirectory;


    function __construct($targetDirectory, $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->targetDirectory = $targetDirectory;

    }

    public function upload(UploadedFile $file, $type)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }


}

?>