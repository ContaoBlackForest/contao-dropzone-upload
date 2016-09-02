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

/**
 * inject the drop zone for content elements
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['load_callback'][] = array(
    'ContaoBlackForest\DropZoneBundle\Controller\ContentSingleSourceController',
    'initializeParseWidget'
);
