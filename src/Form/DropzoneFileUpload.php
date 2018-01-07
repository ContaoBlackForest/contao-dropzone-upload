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

namespace ContaoBlackForest\DropZoneBundle\Form;

use Contao\Config;
use Contao\Controller;
use Contao\FilesModel;
use Contao\FormFileUpload;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Widget;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneDescriptionEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This extend the form file upload.
 */
class DropzoneFileUpload
{
    /**
     * Initialize the dropzone.
     *
     * @param string $buffer The rendered widget.
     *
     * @param Widget $widget The widget.
     *
     * @return string
     */
    public function initialize($buffer, Widget $widget)
    {
        if ('FE' !== TL_MODE
            || !($widget instanceof FormFileUpload)
            || !$widget->dropzoneUpload
        ) {
            return $buffer;
        }

        if ('dropZoneAjax' === Input::post('action')) {
            if ($widget->id !== Input::post('id')) {
                return $buffer;
            }

            header('Content-Type: application/json');
            echo json_encode($this->getResponse($widget));
            exit;
        }

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];

        $property          = $widget->multiple ? substr($widget->name, 0, $widget->name - 2) : $widget->name;
        $uploadFolderEvent = new GetUploadFolderEvent($eventDispatcher, 'frontend', $property);

        if ($widget->storeFile) {
            $filesModel = FilesModel::findByUuid($widget->uploadFolder);

            if ($filesModel) {
                $uploadFolderEvent->setUploadFolder($filesModel->path);
            }
        }

        $eventDispatcher->dispatch(GetUploadFolderEvent::NAME, $uploadFolderEvent);
        $uploadFolder = $uploadFolderEvent->getUploadFolder();

        $dropZoneDescriptionEvent =
            new GetDropZoneDescriptionEvent($eventDispatcher, 'frontend', $property, $uploadFolder);
        $eventDispatcher->dispatch(GetDropZoneDescriptionEvent::NAME, $dropZoneDescriptionEvent);

        $this->includeDropZoneAssets();

        $page = $GLOBALS['objPage'];

        $dropZone                       = new FrontendTemplate('form_field_dropzone');
        $dropZone->url                  = '\'' . $page->getFrontendUrl() . '\'';
        $dropZone->uploadDescription    = $dropZoneDescriptionEvent->getDescription();
        $dropZone->controlInputField    = $widget->id;
        $dropZone->dropzonePreviews     = 'dropzone_previews_' . $widget->id;
        $dropZone->uploadFolder         = $uploadFolder;
        $dropZone->id                   = $widget->id;
        $dropZone->maxFiles             = (!$widget->multipleUpload) ? 1 : ($widget->multipleUploadLimit) ?: 'null';
        $dropZone->dictMaxFilesExceeded =
            sprintf($GLOBALS['TL_LANG']['ERR']['maxFileUpload'], $widget->multipleUploadLimit);

        return $buffer . $dropZone->parse();
    }

    /**
     * Get the response.
     *
     * @param Widget $widget The widget.
     *
     * @return array
     */
    private function getResponse(Widget $widget)
    {
        $widget->extensions = $widget->extensions ?: Config::get('uploadTypes');
        $widget->name       = $widget->multiple ? substr($widget->name, 0, $widget->name - 2) : $widget->name;

        $this->parseGlobalUploadFiles($widget->name);

        $widget->validate();

        $status  = $widget->hasErrors() ? 'error' : 'confirmed';
        $message = $widget->hasErrors() ? implode(' ', $widget->getErrors()) : '';

        return array('message' => $message, 'status' => $status);
    }

    /**
     * Parse global upload files.
     *
     * @return void
     */
    protected function parseGlobalUploadFiles($property)
    {
        $files = array();

        foreach ($_FILES['file'] as $param => $value) {
            if (!is_array($param)) {
                $files[$param] = $value;

                continue;
            }

            $files[$param] = $value;
        }

        $_FILES[$property] = $files;
    }

    /**
     * Include drop zone assets.
     *
     * @return void
     */
    private function includeDropZoneAssets()
    {
        $css        = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/css/dropzone.min.css|static';
        $javascript = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/js/dropzone.min.js|static';

        $GLOBALS['TL_CSS'][md5($css)] = $css;

        $GLOBALS['TL_JAVASCRIPT'][md5($javascript)] = $javascript;

        Controller::loadLanguageFile('tl_files');
    }
}
