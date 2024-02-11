<?php

namespace App\lib;

use App\exception\image\ImageCopyException;
use App\exception\image\ImageUploadException;
use App\exception\InvalidFile;
use Respect\Validation\Validator as v;

class ImageUploadManager
{

    /**
     * Handles the saving of an image to the server.
     *
     * @param string $fileName   The name of the file input field in the form.
     * @param string $uploadPath The directory path where the image will be saved.
     *
     * @return string The filename of the saved image.
     *
     * @throws InvalidFile        If the uploaded file is not a valid image.
     * @throws ImageUploadException If no image is uploaded.
     * @throws ImageCopyException   If the image fails to copy to the specified path.
     */
    public static function upload(string $fileName, string $uploadPath): string
    {

        $file = $_FILES;

        if ($file[$fileName]['error'] == UPLOAD_ERR_NO_FILE) {
            throw new ImageUploadException('Image not uploaded');
        }

        $image = $file[$fileName];

        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);

        //create a unique name for the file
        $imageName = time() . rand(1, 1000) . '.' . $extension;

        $imagePath = $uploadPath . $imageName;

        // Move the uploaded file to the new location
        if (!move_uploaded_file($file[$fileName]["tmp_name"], $imagePath)) {
            throw new ImageCopyException('Failed to save image to path');
        }

        //validate if uploaded file is an image
        if (!v::image()->validate($imagePath)) {
            throw  new InvalidFile('Unsupported File');
        }

        return $imageName;
    }


}