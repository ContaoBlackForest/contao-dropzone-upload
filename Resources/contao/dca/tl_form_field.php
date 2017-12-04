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

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['upload'] = str_replace(
    ';{store_legend',
    ',multipleUpload;{store_legend',
    $GLOBALS['TL_DCA']['tl_form_field']['palettes']['upload']
);

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_form_field']['fields'],
    array(
        'multipleUpload' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['multipleUpload'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 m12'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
    )
);
