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
$GLOBALS['TL_DCA']['tl_calendar']['palettes']['__selector__'] = array_merge(
    $GLOBALS['TL_DCA']['tl_calendar']['palettes']['__selector__'],
    array('dropzoneExtendFolderPath')
);

$GLOBALS['TL_DCA']['tl_calendar']['subpalettes'] = array_merge(
    $GLOBALS['TL_DCA']['tl_calendar']['subpalettes'],
    array(
        'dropzoneExtendFolderPath' => 'dropzoneFolderChunks'
    )
);


$GLOBALS['TL_DCA']['tl_calendar']['palettes']['default'] .= ';{dropzone_legend},dropzoneFolder,dropzoneTitleInFolder,dropzoneAliasInFolder,dropzoneExtendFolderPath';

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_calendar']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_calendar']['fields'],
    array(
        'dropzoneFolder'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radio', 'tl_class' => 'w50 clr'),
            'sql'       => "binary(16) NULL"
        ),
        'dropzoneTitleInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneTitleInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneAliasInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneAliasInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneExtendFolderPath' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneExtendFolderPath'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_calendar']['dropzoneFolderChunks'],
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
        )
    )
);
