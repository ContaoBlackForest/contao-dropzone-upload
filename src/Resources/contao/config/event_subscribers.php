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
use ContaoBlackForest\DropZoneBundle\DataContainer\Table\Common as TableCommon;
use ContaoBlackForest\DropZoneBundle\DataContainer\Table\FileTreeWidget as TableFileTreeWidget;
use ContaoBlackForest\DropZoneBundle\Subscriber\Common as DefaultCommon;

return array(
    new TableCommon(),
    new Description(),
    new TableFileTreeWidget(),
    new DefaultCommon()
);
