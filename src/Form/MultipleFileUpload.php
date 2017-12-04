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

namespace ContaoBlackForest\DropZoneBundle\Form;

use Contao\FormFileUpload;
use Contao\Widget;

/**
 * This extend the form file upload.
 */
class MultipleFileUpload
{
    /**
     * The uploaded files.
     *
     * @var array
     */
    protected $uploadFiles;

    /**
     * Register the load form field hook.
     *
     * @param array  $fields The form fields.
     *
     * @return array
     */
    public function registerLoadFormFieldHook(array $fields)
    {
        foreach ($fields as $field) {
            if ('upload' !== $field->type) {
                continue;
            }

            $GLOBALS['TL_HOOKS']['loadFormField']['upload_' . $field->id] = array(get_class($this), 'multipleUpload');
        }

        return $fields;
    }

    /**
     * Manipulate the widget for multiple uploads.
     * If uploads available, then validate the widget for each file to store it.
     *
     * @param Widget $widget The widget.
     *
     * @return Widget
     */
    public function multipleUpload(Widget $widget)
    {
        if (!($widget instanceof FormFileUpload)
            || !$widget->multipleUpload
        ) {
            return $widget;
        }

        unset($GLOBALS['TL_HOOKS']['loadFormField']['upload_' . $widget->id]);

        // Don´t rename the widget if the files available.
        if (!isset($_FILES[$widget->name])) {
            $widget->name .= '[]';
            $widget->addAttribute('multiple', 'multiple');
        } else {
            // Redefine the the uploads for the validator.
            $this->uploadFiles = $_FILES;
        }

        while (count($this->uploadFiles[$widget->name]['name'])) {
            foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $key) {
                $_FILES[$widget->name][$key] = array_splice($this->uploadFiles[$widget->name][$key], -1)[0];
            }

            $widget->validate();

            if ($widget->hasErrors()) {
                break;
            }
        }

        return $widget;
    }
}
