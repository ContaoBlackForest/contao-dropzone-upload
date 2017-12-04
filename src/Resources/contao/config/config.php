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
 * Contao hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array(
    'ContaoBlackForest\DropZoneBundle\Controller\InitializeController',
    'initializePropertyLoadCallback'
);

$GLOBALS['TL_HOOKS']['executePreActions'][] = array(
    'ContaoBlackForest\DropZoneBundle\Controller\UploadController',
    'upload'
);

$GLOBALS['TL_HOOKS']['compileFormFields'][] = array(
    'ContaoBlackForest\DropZoneBundle\Form\MultipleFileUpload',
    'registerLoadFormFieldHook'
);
