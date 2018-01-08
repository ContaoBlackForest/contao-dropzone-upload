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
use Contao\Files;
use Contao\FilesModel;
use Contao\FileUpload;
use Contao\Folder;
use Contao\Form;
use Contao\FormFileUpload;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Message;
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
     * Prepare the form data bridge.
     *
     * @param array      $submitted The submitted data.
     *
     * @param array      $labels    The labels.
     *
     * @param Form|array $argument1 The form|The form fields.
     *
     * @param array|Form $argument2 The form fields|The form.
     *
     * @return void
     */
    public function prepareFormData(array $submitted, array $labels, $argument1, $argument2)
    {
        // Contao 3.5
        if (!class_exists('Contao\CoreBundle\ContaoCoreBundle')) {
            $this->prepareFormDataExecute($submitted, $labels, $argument1, $argument2);

            return;
        }

        $this->prepareFormDataExecute($submitted, $labels, $argument2, $argument1);
    }

    /**
     * Prepare the form data for move the uploaded temporary files.
     *
     * @param array $submitted  The submitted data.
     *
     * @param array $labels     The labels.
     *
     * @param Form  $form       The form.
     *
     * @param array $formFields The form fields.
     *
     * @return void
     */
    private function prepareFormDataExecute(array $submitted, array $labels, Form $form, array $formFields)
    {
        $tmpFolder = 'system' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'dropzone';
        $tmpFolder .= DIRECTORY_SEPARATOR . Input::post('REQUEST_TOKEN');
        $tmpFolder .= DIRECTORY_SEPARATOR . $form->id;

        if (!is_dir(TL_ROOT . DIRECTORY_SEPARATOR . $tmpFolder)) {
            return;
        }

        $field = null;
        foreach ($formFields as $formField) {
            if (!is_dir(TL_ROOT . DIRECTORY_SEPARATOR . $tmpFolder . DIRECTORY_SEPARATOR . $formField->id)) {
                continue;
            }

            $field = $formField;
            break;
        }
        if (!$field
            || !$field->dropzoneUpload
        ) {
            return;
        }

        $tmpFolder .= DIRECTORY_SEPARATOR . $field->id;

        $files = $this->scanFolder($tmpFolder);
        if (count($files) < 1) {
            return;
        }

        $filesSystem = Files::getInstance();
        foreach ($files as $file) {
            $source      = $file;
            $destination = str_replace($tmpFolder . DIRECTORY_SEPARATOR, '', $file);

            // Do not overwrite existing files
            if ($field->doNotOverwrite && file_exists(TL_ROOT . DIRECTORY_SEPARATOR . $destination)) {
                $path      = substr($destination, 0, strrpos($destination, DIRECTORY_SEPARATOR));
                $file      = substr($destination, strrpos($destination, DIRECTORY_SEPARATOR) + 1);
                $filename  = substr($file, 0, strrpos($file, '.'));
                $extension = substr($file, strrpos($file, '.') + 1);

                $destinationFiles =
                    preg_grep(
                        '/^' . preg_quote($filename, '/') . '.*\.' . preg_quote($extension, '/') . '/',
                        scan(TL_ROOT . DIRECTORY_SEPARATOR . $path)
                    );

                $offset = 1;
                foreach ($destinationFiles as $destinationFile) {
                    if (preg_match('/__[0-9]+\.' . preg_quote($extension, '/') . '$/', $destinationFile)) {
                        $destinationFile = str_replace('.' . $extension, '', $destinationFile);
                        $intValue        = (int) substr($destinationFile, (strrpos($destinationFile, '_') + 1));

                        $offset = max($offset, $intValue);
                    }
                }

                $destination = $path . DIRECTORY_SEPARATOR . $filename . '__' . ++$offset . '.' . $extension;
            }

            if ($filesSystem->rename($source, $destination)) {
                $filesSystem->chmod($destination, Config::get('defaultFileChmod'));

                // Notify the user
                Controller::log('File "' . $destination . '" has been uploaded', __METHOD__, TL_FILES);
            }

            $this->removeEmptyDirectories(dirname($source));
        }
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

        $uploadTypes = Config::get('uploadTypes');
        Config::set('uploadTypes', $widget->extensions);

        $tmpFolder = 'system' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'dropzone';
        $tmpFolder .= DIRECTORY_SEPARATOR . Input::post('REQUEST_TOKEN');
        $tmpFolder .= DIRECTORY_SEPARATOR . $widget->pid;
        $tmpFolder .= DIRECTORY_SEPARATOR . $widget->id;
        $tmpFolder .= DIRECTORY_SEPARATOR . Input::post('uploadFolder');

        new Folder($tmpFolder);

        $upload = new FileUpload();
        $upload->setName($widget->name);
        $uploads = $upload->uploadTo($tmpFolder);

        Config::set('uploadTypes', $uploadTypes);

        $status = 'error';

        if (count($uploads) > 0) {
            $status = 'confirmed';
        }

        $message = Message::generate();
        Message::reset();

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
                $files[$param][] = $value;

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

    /**
     * Scan files in the folder. Hidden files where excluded.
     *
     * @param string $folder The folder.
     *
     * @return array|null
     */
    private function scanFolder($folder)
    {
        if (!file_exists(TL_ROOT . DIRECTORY_SEPARATOR . $folder)) {
            return null;
        }

        $files = array();
        foreach (scan(TL_ROOT . DIRECTORY_SEPARATOR . $folder, true) as $file) {
            if (is_dir(TL_ROOT . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $file)) {
                $files = array_merge($files, $this->scanFolder($folder . DIRECTORY_SEPARATOR . $file));

                continue;
            }

            // Excluded hidden file.
            if (strncmp($file, '.', 1) === 0) {
                continue;
            }

            $files[] = $folder . DIRECTORY_SEPARATOR . $file;
        }

        return $files;
    }

    /**
     * Remove all empty trailing path.
     *
     * @param string $folder The folder.
     *
     * @return void
     */
    private function removeEmptyDirectories($folder)
    {
        if (!Input::post('REQUEST_TOKEN')
            || count($this->scanFolder($folder))
        ) {
            return;
        }

        Files::getInstance()->rrdir($folder);

        $chunks           = explode(DIRECTORY_SEPARATOR, $folder);
        $currentDirectory = array_pop($chunks);

        // Stop remove directory by the temporary dropzone Folder.
        if ($currentDirectory === Input::post('REQUEST_TOKEN')) {
            return;
        }

        $this->removeEmptyDirectories(implode(DIRECTORY_SEPARATOR, $chunks));
    }
}
