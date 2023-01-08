<?php
/**
 * Copyright Mons Agency Ltd. Some rights reserved.
 * See copying.md for details.
 */

namespace Mons\Scss\Model\View\Page\Config\ClientSideScssCompilation;

use Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation\Renderer as ParentRenderer;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Page\Config;

/**
 * Page config Renderer model
 */
class Renderer extends ParentRenderer
{
    /**
     * @inheritDoc
     */
    protected function addDefaultAttributes($contentType, $attributes)
    {
        $rel = $contentType == 'scss' ? 'stylesheet/scss' : '';

        if ($rel) {
            return ' rel="' . $rel . '" type="text/css" ' . ($attributes ?: ' media="all"');
        }

        return parent::addDefaultAttributes($contentType, $attributes);
    }

    /**
     * @inheritDoc
     */
    protected function getAssetContentType(AssetInterface $asset)
    {
        if ($asset->getContentType() !== 'scss') {
            return parent::getAssetContentType($asset);
        }

        return $asset->getSourceContentType();
    }
}
