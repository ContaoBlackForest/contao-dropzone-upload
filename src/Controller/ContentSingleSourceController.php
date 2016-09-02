<?php

/**
 * Copyright © ContaoBlackForest
 *
 * @package   contao-dropzone-upload
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2014-2016 ContaoBlackForest
 */

namespace ContaoBlackForest\DropZone\Controller;

use Contao\FileTree;

class ContentSingleSourceController extends AbstractController
{
    protected $parseWidget = 'tl_content.singleSRC';

    protected $folder = 'files/tiny_templates';

    protected $uploadMultiple = 'false';

    protected $maxFiles = 1;

    public function injectDropZone($buffer, FileTree $widget)
    {
        return parent::injectDropZone($buffer, $widget);
    }
}
