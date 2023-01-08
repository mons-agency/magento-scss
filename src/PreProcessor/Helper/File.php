<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\PreProcessor\Helper;

use Magento\Framework\View\Asset\File as AssetFile;
use Magento\Framework\View\Asset\File\NotFoundException;

class File
{
    /**
     * Checks if the asset file exists
     *
     * @param AssetFile $asset
     * @return boolean
     */
    public function assetFileExists(AssetFile $asset): bool
    {
        try {
            $asset->getSourceFile();
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Transforms the file into underscore notation (partial)
     *
     * @param string $path
     * @return string
     */
    public function getUnderscoreNotation(string $path): string
    {
        $pathInfo = pathinfo($path);

        return !$pathInfo ? $path : $pathInfo['dirname'] . '/_' . $pathInfo['basename'];
    }

    /**
     * Checks if the file to import is a partial
     *
     * @param string $filePath
     * @return bool
     */
    public function isPartial(string $filePath): bool
    {
        $basename = pathinfo($filePath, PATHINFO_BASENAME);

        return !$basename ? false : $basename[0] === '_';
    }

    /**
     * Resolves extension of imported asset according to exact format
     *
     * @param string $fileId
     * @param string $contentType
     * @return string
     */
    public function fixFileExtension(string $fileId, string $contentType): string
    {
        if (!pathinfo($fileId, PATHINFO_EXTENSION)) {
            $fileId .= '.' . $contentType;
        }

        return $fileId;
    }
}
