<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\Plugin\PreProcessor\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Config;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Filesystem;

/**
 * Workaround to enable SCSS map files: duplicates the source files also into pub/static.
 * Not the perfect solution but simplier and cleaner (avoid redundant code).
 */
class TemporaryPlugin
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $tmpDirectory;

    /**
     * @param Config $config
     * @param State $appState
     * @param Filesystem $filesystem
     */
    public function __construct(
        Config $config,
        State $appState,
        Filesystem $filesystem
    )
    {
        if ($appState->getMode() === State::MODE_DEVELOPER) {
            $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        }
    }

    /**
     * @param Temporary $subject
     * @param string $relativePath
     * @param string $contents
     * @return array
     */
    public function beforeCreateFile(Temporary $subject,  string $relativePath, string $contents)
    {
        if ($this->tmpDirectory && pathinfo($relativePath, PATHINFO_EXTENSION) === 'scss') {
            if (!$this->tmpDirectory->isExist($relativePath)) {
                // $this->tmpDirectory->deleteFile($relativePath);
                $this->tmpDirectory->writeFile($relativePath, $contents);
            }

            $this->tmpDirectory->getAbsolutePath($relativePath);
        }

        return [$relativePath, $contents];
    }
}
