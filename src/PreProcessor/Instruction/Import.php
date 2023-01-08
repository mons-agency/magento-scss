<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\PreProcessor\Instruction;

use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;
use Magento\Framework\Css\PreProcessor\Instruction\Import as ParentImport;
use Magento\Framework\View\Asset\File as AssetFile;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\NotationResolver\Module as NotationResolver;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Mons\Scss\PreProcessor\Helper\File as FileHelper;

class Import extends ParentImport
{
    /**
     * @param NotationResolver $notationResolver
     * @param RelatedGenerator $relatedFileGenerator
     * @param AssetRepository $assetRepository
     * @param FileHelper $fileHelper;
     */
    public function __construct(
        NotationResolver $notationResolver,
        RelatedGenerator $relatedFileGenerator,
        protected AssetRepository $assetRepository,
        protected FileHelper $fileHelper
    )
    {
        parent::__construct($notationResolver, $relatedFileGenerator);
    }

    /**
     * Return the replacement of original @import directive
     *
     * @param array $matchedContent
     * @param LocalInterface $asset
     * @param string $contentType
     * @return string
     */
    protected function replace(array $matchedContent, LocalInterface $asset, $contentType)
    {
        $matchedFileId = $this->fixFileExtension($matchedContent['path'], $contentType);
        $relatedAsset = $this->assetRepository->createRelated($matchedFileId, $asset);

        if ($this->fileHelper->assetFileExists($relatedAsset)) {
            return parent::replace($matchedContent, $asset, $contentType);
        }

        $matchedContent['path'] = $this->fileHelper->getUnderscoreNotation($matchedContent['path']);

        return parent::replace($matchedContent, $asset, $contentType);
    }
}
