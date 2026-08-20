<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ImageOptimizerService
{
    /**
     * Optimiza y guarda una imagen reduciendo dimensiones excesivas (máx 2000px) y comprimiendo el peso.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param int $maxDimension
     * @param int $quality
     * @return string Ruta relativa pública (ej. /storage/logos/empresa_logo_123.png)
     */
    public function optimizarYGuardar(UploadedFile $file, string $folder = 'logos', int $maxDimension = 2000, int $quality = 85): string
    {
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'jpeg') $ext = 'jpg';

        // Directorio destino en storage/app/public/
        $destinationPath = storage_path('app/public/' . $folder);
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        // También asegurar simlink en public/storage/logos si aplica
        $publicStoragePath = public_path('storage/' . $folder);
        if (!File::exists($publicStoragePath)) {
            File::makeDirectory($publicStoragePath, 0755, true, true);
        }

        // Crear recurso GD según el tipo de imagen
        $srcImage = match ($ext) {
            'png' => @imagecreatefrompng($file->getRealPath()),
            'webp' => @imagecreatefromwebp($file->getRealPath()),
            'jpg', 'jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            default => null,
        };

        $filename = 'empresa_logo_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
        $targetFile = $destinationPath . '/' . $filename;
        $publicTargetFile = $publicStoragePath . '/' . $filename;

        if ($srcImage) {
            $origWidth = imagesx($srcImage);
            $origHeight = imagesy($srcImage);

            // Reescalar si supera la dimensión máxima de 2000px conservando aspect ratio
            if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
                if ($origWidth >= $origHeight) {
                    $newWidth = $maxDimension;
                    $newHeight = (int)round(($origHeight * $maxDimension) / $origWidth);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int)round(($origWidth * $maxDimension) / $origHeight);
                }

                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                // Conservar transparencia para PNG y WEBP
                if ($ext === 'png' || $ext === 'webp') {
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                    $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                    imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagedestroy($srcImage);
                $srcImage = $dstImage;
            }

            // Guardar recurso procesado
            match ($ext) {
                'png' => imagepng($srcImage, $targetFile, 8),
                'webp' => imagewebp($srcImage, $targetFile, $quality),
                default => imagejpeg($srcImage, $targetFile, $quality),
            };

            imagedestroy($srcImage);

            // Copiar a la carpeta pública directa para acceso instantáneo sin demoras de symlink
            @copy($targetFile, $publicTargetFile);
        } else {
            // Si por algún motivo GD no puede decodificarlo, guardar archivo original
            $file->storeAs('public/' . $folder, $filename);
            @copy($destinationPath . '/' . $filename, $publicTargetFile);
        }

        return '/storage/' . $folder . '/' . $filename;
    }

    /**
     * Elimina el archivo físico del almacenamiento si existe.
     *
     * @param string|null $path
     * @return void
     */
    public function eliminarArchivo(?string $path): void
    {
        if (!$path) return;

        // Limpiar prefijo /storage/ o storage/
        $cleanPath = ltrim(str_replace('/storage/', '', $path), '/');
        
        $storageFile = storage_path('app/public/' . $cleanPath);
        $publicFile = public_path('storage/' . $cleanPath);

        if (File::exists($storageFile)) {
            @File::delete($storageFile);
        }

        if (File::exists($publicFile)) {
            @File::delete($publicFile);
        }

        // En caso de que apunte a /assets/images/
        if (str_contains($path, '/assets/images/')) {
            $assetFile = public_path(ltrim($path, '/'));
            if (File::exists($assetFile) && !str_contains($path, 'default')) {
                @File::delete($assetFile);
            }
        }
    }
}
