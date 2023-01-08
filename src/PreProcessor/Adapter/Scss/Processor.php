<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\PreProcessor\Adapter\Scss;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Config;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

/**
 * Processor class
 */
class Processor implements ContentProcessorInterface
{
    /**
     * @var bool
     */
    private $developerMode;
    /**
     * @var string
     */
    private $includePath;
    /**
     * @var array
     */
    private $sourceMapOptions;

    /**
     * @param LoggerInterface $logger
     * @param State $appState
     * @param Source $assetSource
     * @param Temporary $temporaryFile
     * @param Config $config
     * @param Filesystem $filesystem
     */
    public function __construct(
        private LoggerInterface $logger,
        private Source $assetSource,
        private Temporary $temporaryFile,
        Config $config,
        Filesystem $filesystem,
        State $appState
    )
    {
        $sourceMapBasePath = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('view_preprocessed/pub');

        $this->developerMode = $appState->getMode() === State::MODE_DEVELOPER;
        $this->includePath = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath($config->getMaterializationRelativePath());
        $this->sourceMapOptions = [
            // 'outputSourceFiles' => true,
            'sourceMapBasepath' => $sourceMapBasePath,
            'sourceRoot' => '/',
        ];
    }

    /**
     * Process file content
     *
     * @param File $asset
     * @return string
     * @throws ContentProcessorException
     */
    public function processContent(File $asset)
    {
        $path = $asset->getPath();

        try {
            $content = $this->assetSource->getContent($asset);

            // same behavior as LESS processor
            if (!$content || trim($content) === '') {
                // return '';
                throw new ContentProcessorException(
                    new Phrase('Compilation from source - SCSS file is empty: ' . $path)
                );
            }

            $parser = $this->getParser(dirname($asset->getSourceFile()));
            $tmpFilePath = $this->temporaryFile->createFile($path, $content);

            gc_disable();
            $content = $parser->compile($content, $tmpFilePath);
            gc_enable();

            if (trim($content) === '') {
                throw new ContentProcessorException(
                    new Phrase('Compilation from source - SCSS file is empty: ' . $path)
                );
            }

            return $content;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            throw new ContentProcessorException(new Phrase($e->getMessage()));
        }
    }

    /**
     * Returns SCSS parser instance
     *
     * @param string $includePath
     * @return Compiler
     */
    private function getParser(string $includePath): Compiler {
        $parser = new Compiler();

        // parser configuration
        $parser->setOutputStyle($this->developerMode ? OutputStyle::EXPANDED : OutputStyle::COMPRESSED);
        // $parser->addImportPath($this->includePath);
        $parser->addImportPath($includePath);
        $parser->setSourceMap($this->developerMode ? Compiler::SOURCE_MAP_INLINE : Compiler::SOURCE_MAP_NONE);
        $parser->setSourceMapOptions($this->sourceMapOptions);

        return $parser;
    }
}
