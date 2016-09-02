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

use ContaoBlackForest\DropZoneBundle\DataContainer\Description;
use ContaoBlackForest\DropZoneBundle\DataContainer\Table\Content;
use ContaoBlackForest\DropZoneBundle\DataContainer\Table\FileTreeWidget;

return [
    new Content(),
    new Description(),
    new FileTreeWidget()
];
