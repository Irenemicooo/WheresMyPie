<?php
class ImageHandler {
    private $maxWidth = 800;
    private $maxHeight = 800;
    private $quality = 80;

    public function processUpload($file, $targetDir) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid parameters.');
        }

        // 檢查檔案大小
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('File too large.');
        }

        // 檢查 MIME 類型
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($file['tmp_name']),
            [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ],
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }

        // 生成唯一檔名
        $filename = sprintf('%s.%s',
            uniqid(),
            $ext
        );

        if (!move_uploaded_file(
            $file['tmp_name'],
            $targetDir . $filename
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

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
