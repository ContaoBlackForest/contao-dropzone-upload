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

$GLOBALS['TL_DCA']['tl_calendar']['palettes']['default'] .= ';{dropzone_legend},dropzoneFolder';

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_calendar']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_calendar']['fields'],
    array(
        'dropzoneFolder' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radiobox', 'tl_class' => 'w50'),
            'sql'       => "blob NULL"
        ),
    )
);
