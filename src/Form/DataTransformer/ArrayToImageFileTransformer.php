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
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Constraints\Collection;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Entity\File;
use \Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArrayToImageFileTransformer implements DataTransformerInterface
{
    private $entityManager, $rootDir, $tempFolder;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->tempFolder = $this->rootDir . '/public/sources/temp/';
    }

    public function reverseTransform($array)
    {
        $uploadedFile = $array['fileContents'];

        if (is_null($uploadedFile)) {
            return null;
        }
        else{
            $imageFile = new ImageFile();
            $type = explode("/",$uploadedFile->getMimeType())[1];
            if($type === 'jpeg'||$type === 'png'){
                $imageFile->setFileContents($uploadedFile);
                $imageFile->setSize($uploadedFile->getSize());
                $imageFile->setType($type);
                return $imageFile;
            }
            else{
                return null;
            }
        }
    }

    public function transform($file)
    {
        if(is_null($file))
            return null;
        else {
            $array = [];
            $array['0']['filecontents'] = $file->getFilecontents();
            return $array;
        }
    }
}