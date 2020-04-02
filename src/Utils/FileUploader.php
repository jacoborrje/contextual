<?php
namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Source;
use App\Entity\Place;
use App\Entity\DatabaseFile;
use \DateTime;
use Imagick;
use Spatie\PdfToText\Pdf;

class FileUploader
{
    private $sourcesDir, $placeimageDir, $publicDir, $tempFolder;
    private $imageService, $entityManager;
    private $text;


    function __construct($publicDir, $sourcesDir, $placeimageDir, ImageService $imageService, EntityManagerInterface $entityManager)
    {
        $this->publicDir = $publicDir;
        $this->sourcesDir = $sourcesDir;
        $this->placeimageDir = $placeimageDir;
        $this->imageService = $imageService;
        $this->tempFolder = $this->publicDir . '/sources/temp/';
        $this->entityManager = $entityManager;
    }

    public function upload(UploadedFile $file, $type, $object)
    {
        if($type === 'pdf'){
            if($file->getMimeType()==='application/pdf'){
                $filename = $this->generatePdfFilename($object);
                try {
                    $file->move($this->publicDir. $this->sourcesDir.$this->generatePdfPath($object), $filename);
                } catch (FileException $e) {
                    echo $e->getMessage();
                }
            }
        }
        else if ($type === 'image'){
            if($file->getMimeType() ==='jpeg' || $file->getMimeType() === 'png'){
                try {
                    $pathname = $this->publicDir . $this->placeimageDir . $filename;

                } catch (FileException $e) {
                    echo $e->getMessage();
                }
            }
        }
        else{
            return false;
        }

        return $filename;
    }

    public function generatePdfFilename(Source $source){
        $filename = $source->getAuthorsAsString(1, true);
        $filename = $filename . "_(" . $source->getTextDate().").pdf";
        return $filename;
    }

    public function generatePdfPath(Source $source){
        $path = $source->getSourcePath();
        return $path;
    }

    public function generatePlaceimageFilename(Place $place){
        $filename = $place->getName();
        $filename = preg_replace("([åäö])", "a", $filename);
        return $filename;
    }

    public function generatePlaceimageThumbnailFilename(Place $place){
        $filename = $place->getName();
        $filename .= "_thumb";
        return $filename;
    }

    public function uploadPlaceImage(Place $place){
        $uploadedFile = $place->getImage()->getFileContents();
        if(!is_null($uploadedFile)){
            $type = explode("/",$uploadedFile->getMimeType())[1];
            if($type === 'jpeg'||$type === 'png'){
                $imageFile = $place->getImage();
                if($type ==='jpeg') $extension = ".jpg";
                else if ($type ==='png') $extension = ".png";
                $filename = $this->generatePlaceimageFilename($place).$extension;
                $path = $this->publicDir . $this->placeimageDir;
                $pathname = $path . $filename;
                $imageFile->setName($filename);
                $imageFile->setType($type);
                $currentTime = new DateTime();
                $currentTime->format('Y-m-d H:i:s');
                $imageFile->setUpdatedAt($currentTime);
                $uploadedFile->move($path, $filename);
                $this->imageService->resizeImage($pathname, 400, 400);
                $uploadedFile = new UploadedFile($pathname,$filename);
                $imageFile->setSize($uploadedFile->getSize());
                $thumbPathname = $path . $this->generatePlaceimageThumbnailFilename($place).$extension;
                $this->imageService->createThumbnail($pathname, $thumbPathname, 250, 300);

                $place->setImage($imageFile);
                return $place;
            }
            else{
                throw new \Exception('It is only possible to upload jpeg and png images!');
            }
        }
        else{
            throw new \Exception('No file was uploaded');
        }
    }

    public function uploadSourcePdf(Source $source){
        $uploadedFile = $source->getFile()->getFileContents();
        if(!is_null($uploadedFile)){
            if(gettype($uploadedFile) === 'array'){
                if ( count($uploadedFile)===0) {
                    $source->setFiles(null);
                    return $source;
                }
                else if (count($uploadedFile)===1){
                    print_r($uploadedFile[0]);
                    $type = explode("/",$uploadedFile[0]->getMimeType())[1];
                    if($type === 'pdf'){
                        $extension = '.pdf';
                        $uploadedFile = $uploadedFile[0];
                    }
                    else if ($type === 'jpeg'){
                        $image = $uploadedFile[0]->getPathname();
                        $pdf = new Imagick($image);
                        $filename = $this->tempFolder.'combined.pdf';
                        $pdf->writeImages($filename, true);
                        $uploadedFile = new UploadedFile($filename, "combined.pdf", null, null,  true);
                    }
                    else{
                        throw new \Exception('The file must be a pdf!');
                    }
                }
                else if (count($uploadedFile)>1){
                    $images = [];
                    foreach($uploadedFile as $file) {
                        $type = explode("/",$file->getMimeType())[1];
                        if($type === 'jpeg') {
                            $images[] = $file->getPathname();
                        }
                    }
                    $extension = '.pdf';
                    $pdf = new Imagick($images);
                    $pdf->setImageFormat('pdf');
                    $filename = $this->tempFolder.'combined.pdf';
                    $pdf->writeImages($filename, true);
                    $uploadedFile = new UploadedFile($filename, "combined.pdf", null, null,  true);
                }
                else{
                    throw new \Exception('Unknown error!');
                }
            }
            else {
                $type = explode("/", $uploadedFile->getMimeType())[1];
                if ($type === 'pdf') {
                    $extension = ".pdf";
                } else {
                    throw new \Exception('The file must be a pdf!');
                }
            }
            $pdfFile = $source->getFile();
            $filename = $this->generatePdfFilename($source);
            $path = $this->publicDir . $this->sourcesDir . $this->generatePdfPath($source);
            $pathname = $path . $filename;
            $pdfFile->setPath($this->generatePdfPath($source));
            $pdfFile->setName($filename);
            $pdfFile->setType('pdf');
            $pdfFile->setSize($uploadedFile->getSize());
            $currentTime = new DateTime();
            $currentTime->format('Y-m-d H:i:s');
            $pdfFile->setUpdatedAt($currentTime);
            $uploadedFile->move($path, $filename);

            if(strlen($source->getTranscription())===0||is_null($source->getTranscription())){
                $text = (new Pdf('/usr/local/bin/pdftotext'))
                    ->setPdf($pathname)
                    ->text();
                    $source->setTranscription($text);
            }
            $source->setFile($pdfFile);
            return $source;
        }
        else{
            throw new \Exception('No file was uploaded');
        }
    }

    public function removePlaceImage(Place $place, DatabaseFile $image){
        $extension = $this->getExtension($image);
        if(file_exists($this->publicDir. $this->placeimageDir.$image->getName()))
            unlink($this->publicDir . $this->placeimageDir.$image->getName());
        echo $this->publicDir . $this->placeimageDir.$this->generatePlaceimageThumbnailFilename($place).$extension;
        if(file_exists($this->publicDir . $this->placeimageDir.$this->generatePlaceimageThumbnailFilename($place).$extension))
            unlink($this->publicDir . $this->placeimageDir.$this->generatePlaceimageThumbnailFilename($place).$extension);
        $place->setImage(null);

        $this->entityManager->persist($place);
        $this->entityManager->flush();
        $this->entityManager->remove($image);
        $this->entityManager->flush();
        return $place;
    }

    public function removePdf(Source $source, DatabaseFile $pdf){
        if(file_exists($this->publicDir.$this->sourcesDir.$pdf->getPathname()))
            unlink($this->publicDir.$this->sourcesDir.$pdf->getPathname());

        $files_in_directory = scandir($this->publicDir.$this->sourcesDir.$pdf->getPath());
        if(count($files_in_directory) <= 2){
            rmdir($this->publicDir.$this->sourcesDir.$pdf->getPath());
        }
        $source->setFile(null);



        $this->entityManager->persist($source);
        $this->entityManager->flush();
        $this->entityManager->remove($pdf);
        $this->entityManager->flush();
        return $source;
    }

    public function getExtension($image){
        $type = $image->getType();

        if($type === 'jpeg')
            return ".jpg";
        else if ($type === 'png')
            return ".png";
        else if ($type === 'pdf')
            return ".pdf";
        else
            return null;
    }

}

?>