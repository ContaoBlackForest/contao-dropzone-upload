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
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'] = array_merge(
    $GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'],
    array('dropzoneExtendFolderPath')
);

$GLOBALS['TL_DCA']['tl_page']['subpalettes'] = array_merge(
    $GLOBALS['TL_DCA']['tl_page']['subpalettes'],
    array(
        'dropzoneExtendFolderPath' => 'dropzoneFolderChunks'
    )
);

foreach (array_keys($GLOBALS['TL_DCA']['tl_page']['palettes']) as $paletteName) {
    if (in_array($paletteName, array('__selector__', 'default', 'error_403', 'error_404'))) {
        continue;
    }

    $GLOBALS['TL_DCA']['tl_page']['palettes'][$paletteName] .= ';{dropzone_legend},dropzoneFolder,dropzoneExtendFolderPath';
}

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_page']['fields'],
    array(
        'dropzoneFolder'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radio', 'tl_class' => 'w50 clr'),
            'sql'       => "binary(16) NULL"
        ),
        'dropzoneExtendFolderPath' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzoneExtendFolderPath'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneFolderChunks'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzoneFolderChunks'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => array(
                'tl_class'     => 'clr',
                'mandatory'    => true,
                'columnFields' => array
                (
                    'chunk' => array
                    (
                        'exclude'   => true,
                        'inputType' => 'text',
                        'search'    => true,
                        'eval'      => array('hideHead' => true)
                    )
                )
            ),
            'sql'       => "text NULL"
        ),
        'dropzoneNotOverride'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzoneNotOverride'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzonePostfix'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzonePostfix'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array(
                'rgxp'      => 'alias',
                'doNotCopy' => true,
                'maxlength' => 24,
                'tl_class'  => 'w50 clr'
            ),
            'sql'       => "varchar(24) NOT NULL default ''"
        ),
        'dropzoneCounterLength'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_page']['dropzoneCounterLength'],
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
        )
    )
);
