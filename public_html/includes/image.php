<?php
class ImageHandler {
    private $maxWidth = 800;
    private $maxHeight = 800;
    private $quality = 80;

    public function processUpload($file, $targetDir) {
        $image = $this->loadImage($file['tmp_name']);
        $image = $this->resize($image);
        $filename = uniqid() . '.jpg';
        $this->save($image, $targetDir . $filename);
        return $filename;
    }

    private function loadImage($path) {
        $info = getimagesize($path);
        switch ($info[2]) {
            case IMAGETYPE_JPEG: return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG: return imagecreatefrompng($path);
            case IMAGETYPE_GIF: return imagecreatefromgif($path);
            default: throw new Exception('Unsupported image type');
        }
    }

    private function resize($image) {
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width <= $this->maxWidth && $height <= $this->maxHeight) {
            return $image;
        }

        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, 
                          $new_width, $new_height, $width, $height);
        return $new_image;
    }

    private function save($image, $path) {
        return imagejpeg($image, $path, $this->quality);
    }
}
