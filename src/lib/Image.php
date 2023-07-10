<?php

namespace App\lib;

use Exception;

class Image {

    static function store($path, $image) {

            $extension = pathinfo($image['name'], PATHINFO_EXTENSION);

            $imageName = time() .rand(1, 1000). '.' . $extension;

            $imagePath =  $path . $imageName;

            // Move the uploaded file to the new location
            if (!move_uploaded_file($image["tmp_name"], $imagePath)) {
                throw new Exception("Failed to save image");
            }

            return $imageName;
    }

    static function storeAll($path,$images){
     
        foreach ($images['tmp_name'] as $index => $tmp_name) {

            $image = array(
                'name' => $images['name'][$index],
                'type' => $images['type'][$index],
                'tmp_name' => $tmp_name,
                'error' => $images['error'][$index],
                'size' => $images['size'][$index]
            );
    
            $names[] = self::store($path,$image);
        }

        return $names;
    }
}
