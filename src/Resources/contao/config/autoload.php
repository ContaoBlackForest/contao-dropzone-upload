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
 * load templates
 */
\Contao\TemplateLoader::addFiles(
    array(
        'be_dropzone'         => 'system/modules/dropzone-upload/templates/backend',
        'form_field_dropzone' => 'system/modules/dropzone-upload/templates/form'
    )
);
