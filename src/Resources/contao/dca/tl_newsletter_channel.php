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
 *
 * Create the array for Contao 4.
 */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['__selector__'] = array_merge(
    (array) $GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['__selector__'],
    array('dropzoneExtendFolderPath')
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['subpalettes'] = array_merge(
    (array) $GLOBALS['TL_DCA']['tl_newsletter_channel']['subpalettes'],
    array(
        'dropzoneExtendFolderPath' => 'dropzoneFolderChunks'
    )
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['default'] .= ';{dropzone_legend},dropzoneFolder,dropzoneTitleInFolder,dropzoneAliasInFolder,dropzoneExtendFolderPath';

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_newsletter_channel']['fields'],
    array(
        'dropzoneFolder'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radio', 'tl_class' => 'w50 clr'),
            'sql'       => "binary(16) NULL"
        ),
        'dropzoneTitleInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['dropzoneTitleInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneAliasInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['dropzoneAliasInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneExtendFolderPath' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['dropzoneExtendFolderPath'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_newsletter_channel']['dropzoneFolderChunks'],
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
