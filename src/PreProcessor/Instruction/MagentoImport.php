<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\Preprocessor\Instruction;

use Magento\Framework\Css\PreProcessor\ErrorHandlerInterface;
use Magento\Framework\Css\PreProcessor\File\Collector\Aggregated as FileCollector;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Mons\Scss\PreProcessor\Helper\File as FileHelper;

class MagentoImport implements PreProcessorInterface
{
    /**
     * @param DesignInterface $design
     * @param FileCollector $fileSource
     * @param ErrorHandlerInterface $errorHandler
     * @param AssetRepository $assetRepository
     * @param ThemeProviderInterface $themeProvider
     * @param FileHelper $fileHelper
     */
    public function __construct(
        protected DesignInterface $design,
        protected FileCollector $fileSource,
        protected ErrorHandlerInterface $errorHandler,
        protected AssetRepository $assetRepository,
        protected ThemeProviderInterface $themeProvider,
        protected FileHelper $fileHelper
    )
    {}

    /**
     * @inheritdoc
     */
    public function process(Chain $chain)
    {
        $asset = $chain->getAsset();
        $contentType = $chain->getContentType();
        $replaceCallback = function ($matchContent) use ($asset, $contentType) {
            // normalize
            $matchedFileId = $matchContent['path'];

            if (!$this->fileHelper->isPartial($matchedFileId)) {
                $matchedFileId = $this->fileHelper->getUnderscoreNotation($matchedFileId);
            }

            $matchContent['path'] = $this->fileHelper->fixFileExtension($matchedFileId, $contentType);

            return $this->replace($matchContent, $asset);
        };

        $chain->setContent(preg_replace_callback(
            \Magento\Framework\Css\PreProcessor\Instruction\MagentoImport::REPLACE_PATTERN,
            $replaceCallback,
            $chain->getContent()
        ));
    }

    /**
     * @inheritdoc
     */
    protected function replace(array $matchedContent, LocalInterface $asset)
    {
        $imports = [];

        try {
            $matchedFileId = $matchedContent['path'];
            $relatedAsset = $this->assetRepository->createRelated($matchedFileId, $asset);
            $resolvedPath = $relatedAsset->getFilePath();
            $files = $this->fileSource->getFiles($this->getTheme($relatedAsset), $resolvedPath);

            /** @var \Magento\Framework\View\File */
            foreach ($files as $file) {
                $imports[] = $file->getModule()
                    ? "@import '{$file->getModule()}::{$resolvedPath}';"
                    : "@import '{$matchedFileId}';";
            }
        } catch (\Exception $e) {
            $this->errorHandler->processException($e);
        }

        return implode("\n", $imports);
    }

    /**
     * Get theme model based on the information from asset
     *
     * @param LocalInterface $asset
     * @return ThemeInterface
     */
    protected function getTheme(LocalInterface $asset): ThemeInterface
    {
        $context = $asset->getContext();

        if ($context instanceof FallbackContext) {
            return $this->themeProvider->getThemeByFullPath(
                $context->getAreaCode() . '/' . $context->getThemePath()
            );
        }

        return $this->design->getDesignTheme();
    }
}
