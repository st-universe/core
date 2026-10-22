<?php

declare(strict_types=1);

namespace Stu\Lib\Upload;

use finfo;
use GdImage;
use Throwable;

class PngUpload
{
    public function readUploaded(string $path, int $error, int $maximumBytes, int $maximumWidth, int $maximumHeight, int $maximumPixels): ?GdImage
    {
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($path)) {
            return null;
        }

        return $this->readFile($path, $maximumBytes, $maximumWidth, $maximumHeight, $maximumPixels);
    }

    public function readFile(string $path, int $maximumBytes, int $maximumWidth, int $maximumHeight, int $maximumPixels): ?GdImage
    {
        if (!is_file($path)) {
            return null;
        }
        $size = filesize($path);
        if ($size === false || $size === 0 || $size > $maximumBytes) {
            return null;
        }
        $metadata = @getimagesize($path);
        if ($metadata === false || $metadata[2] !== IMAGETYPE_PNG
            || $metadata[0] <= 0 || $metadata[1] <= 0
            || $metadata[0] > $maximumWidth || $metadata[1] > $maximumHeight
            || $metadata[0] * $metadata[1] > $maximumPixels
            || new finfo(FILEINFO_MIME_TYPE)->file($path) !== 'image/png') {
            return null;
        }
        try {
            $image = @imagecreatefrompng($path);
        } catch (Throwable) {
            return null;
        }
        if ($image === false) {
            return null;
        }
        imagesavealpha($image, true);

        return $image;
    }

    public function write(GdImage $image, string $path, int $maximumBytes): bool
    {
        if (!imagepng($image, $path, 9)) {
            return false;
        }
        clearstatcache(true, $path);
        $size = filesize($path);
        if ($size === false || $size > $maximumBytes) {
            unlink($path);
            return false;
        }

        return true;
    }
}
