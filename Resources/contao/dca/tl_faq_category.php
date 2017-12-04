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

$GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default'] .= ';{dropzone_legend},dropzoneFolder';

/**
 * Add Fields
 */
$GLOBALS['TL_DCA']['tl_faq_category']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_faq_category']['fields'],
    array(
        'dropzoneFolder' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_faq_category']['dropzoneFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('fieldType' => 'radiobox', 'tl_class' => 'w50'),
            'sql'       => "blob NULL"
        ),
    )
);
