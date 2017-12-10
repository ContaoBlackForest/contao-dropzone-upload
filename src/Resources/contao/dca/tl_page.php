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
 * Inject field in the palette.
 */
foreach (array_keys($GLOBALS['TL_DCA']['tl_page']['palettes']) as $paletteName) {
    if (in_array($paletteName, array('__selector__', 'default', 'error_403', 'error_404'))) {
        continue;
    }

    $GLOBALS['TL_DCA']['tl_page']['palettes'][$paletteName] .= ';{dropzone_legend},dropzoneFolder';
}

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_page']['fields'],
    array(
        'dropzoneFolder' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_page']['dropzoneFolder'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('fieldType'=>'radio', 'tl_class' => 'w50'),
            'sql'                     => "binary(16) NULL"
        ),
    )
);
