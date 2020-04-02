<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use App\Entity\DatabaseFile;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Constraints\Collection;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Entity\File;
use \Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArrayToUploadableFileTransformer implements DataTransformerInterface
{
    private $entityManager, $rootDir, $tempFolder;

    public function __construct(EntityManagerInterface $entityManager, $rootDir)
    {
        $this->entityManager = $entityManager;
        $this->rootDir = $rootDir;
        $this->tempFolder = $this->rootDir . '/public/sources/temp/';

    }

    public function reverseTransform($array)
    {
        $fileContents = $array['fileContents'];
        echo "Reverse transforming!";
        if (null === $fileContents || count($fileContents)===0) {
            return null;
        }
        else if (count($fileContents)===1){
            echo "Found one file!<br>";
            $pdfFile = new DatabaseFile();
            $uploadedFile = $fileContents[0];
            //echo $uploadedFile->getErrorMessage();
            $type = explode("/",$uploadedFile->getMimeType())[1];
            if($type === 'pdf'){
                $pdfFile->setFileContents($uploadedFile);
                return $pdfFile;
            }
            else{
                return null;
            }
        }
        else if (count($fileContents)>1){
            echo "found more than one file!<br>";
            $images = [];
            foreach($fileContents as $file) {
                $type = explode("/",$file->getMimeType())[1];
                if($type === 'jpeg') {
                    $images[] = $file->getPathname();
                }
            }

            $pdf = new Imagick($images);
            $pdf->setImageFormat('pdf');
            $filename = $this->tempFolder.'combined.pdf';
            $pdf->writeImages($filename, true);

            $file = new UploadedFile($filename, "combined.pdf", null, null,  true);

            $pdfFile = new DatabaseFile();
            $pdfFile->setFileContents($file);
            return $pdfFile;
        }
        else{
            return null;
        }
    }

    public function transform($file)
    {
        if(is_null($file)){
            echo "File is null";
            return null;
        }
        else
            return $file;
    }
}