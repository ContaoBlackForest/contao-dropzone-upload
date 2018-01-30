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
$GLOBALS['TL_DCA']['tl_faq_category']['palettes']['__selector__'] = array_merge(
    $GLOBALS['TL_DCA']['tl_faq_category']['palettes']['__selector__'],
    array('dropzoneNotOverride', 'dropzoneExtendFolderPath')
);

$GLOBALS['TL_DCA']['tl_faq_category']['subpalettes'] = array_merge(
    $GLOBALS['TL_DCA']['tl_faq_category']['subpalettes'],
    array(
        'dropzoneNotOverride'      => 'dropzonePostfix,dropzoneCounterLength',
        'dropzoneExtendFolderPath' => 'dropzoneFolderChunks'
    )
);


$GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default'] .= ';{dropzone_legend},dropzoneFolder,dropzoneTitleInFolder,dropzoneAliasInFolder,dropzoneExtendFolderPath,dropzoneNotOverride';

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_faq_category']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_faq_category']['fields'],
    array(
        'dropzoneFolder'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radio', 'tl_class' => 'w50 clr'),
            'sql'       => "binary(16) NULL"
        ),
        'dropzoneTitleInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneTitleInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneAliasInFolder'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneAliasInFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array(
                'tl_class' => 'w50 clr'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'dropzoneExtendFolderPath' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneExtendFolderPath'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneFolderChunks'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneNotOverride'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzonePostfix'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneCounterLength'],
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
