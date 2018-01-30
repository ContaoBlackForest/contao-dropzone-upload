<?php

/**
 * Copyright © ContaoBlackForest
 *
 * @package   contao-dropzone-upload
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2014-2018 ContaoBlackForest
 */

/**
 * Inject field in the palette.
 */
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'] = array_merge(
    $GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'],
    array('multipleUpload', 'dropzoneUpload')
);

$GLOBALS['TL_DCA']['tl_form_field']['subpalettes'] = array_merge(
    $GLOBALS['TL_DCA']['tl_form_field']['subpalettes'],
    array(
        'multipleUpload'      => 'multipleUploadLimit'
    )
);

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['upload'] = str_replace(
    ';{store_legend',
    ',multipleUpload,dropzoneUpload;{store_legend',
    $GLOBALS['TL_DCA']['tl_form_field']['palettes']['upload']
);

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_form_field']['fields'],
    array(
        'multipleUpload'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['multipleUpload'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'submitOnChange' => true,
                'tl_class'       => 'w50 m12'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'multipleUploadLimit'   => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['multipleUploadLimit'],
            'default'   => 0,
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array(
                'rgxp'      => 'natural',
                'doNotCopy' => true,
                'maxlength' => 24,
                'tl_class'  => 'w50',
                'mandatory' => true
            ),
            'sql'       => "varchar(24) NOT NULL default ''"
        ),
        'dropzoneUpload'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['dropzoneUpload'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 m12'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        )
    )
);
