<?php
/**
 * Created by PhpStorm.
 * User: Toshiba
 * Date: 27.2.2019 г.
 * Time: 16:29 ч.
 */

namespace AppBundle\Service;

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function uploadImage(UploadedFile $uploadedFile, $targetDirectory)
    {
        $fileName = md5(uniqid()). '.' . $uploadedFile->guessExtension();

        try {
            $image = new ImageManager();

            $image
                ->make($uploadedFile->getRealPath())
                ->resize(1024, 768)
                ->save($targetDirectory . '/' . $fileName);

        }catch (FileException $exception) {

        }

        return $fileName;
    }
}