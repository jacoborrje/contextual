<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use App\Entity\ImageFile;
use App\Entity\PdfFile;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Constraints\Collection;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Entity\File;
use \Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VichImageToImageFileTransformer implements DataTransformerInterface
{
    private $entityManager, $rootDir, $tempFolder;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->tempFolder = $this->rootDir . '/public/sources/temp/';
    }

    public function reverseTransform($VichImageFile)
    {
        echo "Test";
        print_r($VichImageFile);
        $fileContents = null;

        if (null === $fileContents || count($fileContents)===0) {
            return null;
        }
        else if (count($fileContents)===1){
            $pdfFile = new ImageFile();
            $uploadedFile = $fileContents[0];
            //echo $uploadedFile->getErrorMessage();
            $type = explode("/",$uploadedFile->getMimeType())[1];
            if($type === 'pdf'){
                $pdfFile->setFileContents($uploadedFile);
                $pdfFile->setType($type);
                return $pdfFile;
            }
            else{
                return null;
            }
        }
        else if (count($fileContents)>1){
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

            $pdfFile = new PdfFile();
            $pdfFile->setFileContents($file);
            $pdfFile->setType('pdf');
            return $pdfFile;
        }
        else{
            return null;
        }
    }

    public function transform($file)
    {
        return [$file];
    }
}