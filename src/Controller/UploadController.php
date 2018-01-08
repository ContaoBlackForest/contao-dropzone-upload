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

namespace ContaoBlackForest\DropZoneBundle\Controller;

use Contao\Config;
use Contao\Controller;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\FileUpload;
use Contao\Folder;
use Contao\Input;
use Contao\Message;
use Contao\RequestToken;
use ContaoBlackForest\DropZoneBundle\Event\GetFilenameEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetFileTreeWidgetEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The upload controller.
 */
class UploadController
{
    /**
     * Upload the file to the upload folder.
     *
     * @param string $action The action.
     *
     * @return void
     */
    public function upload($action)
    {
        if ($action !== 'dropZoneAjax'
            || !Input::get('dropfield')
            || !Input::get('dropfolder')
            || RequestToken::validate(Input::get('ref'))
        ) {
            return;
        }

        $this->parseGlobalUploadFiles();

        if (!is_dir(TL_ROOT . '/' . Input::get('dropfolder'))) {
            new Folder(Input::get('dropfolder'));
        }

        // Set the allowed upload file types for the widget.
        if (Input::post('extensions')) {
            $uploadTypes = Config::get('uploadTypes');
            Config::set('uploadTypes', Input::post('extensions'));
        }

        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];

        $fileNameEvent = new GetFilenameEvent(
            $eventDispatcher, Input::post('table'), $_FILES['files']['name'][0]
        );
        $eventDispatcher->dispatch(GetFilenameEvent::NAME, $fileNameEvent);

        $_FILES['files']['name'][0] = $fileNameEvent->getFilename();

        $upload  = new FileUpload();
        $uploads = $upload->uploadTo(Input::get('dropfolder'));

        unset($_FILES['files']);

        // Reset the defined upload types.
        if (Input::post('extensions')) {
            Config::set('uploadTypes', $uploadTypes);
        }

        Dbafs::syncFiles();

        header('Content-Type: application/json');
        echo json_encode($this->getResponse($uploads));
        exit;
    }

    /**
     * Parse global upload files.
     *
     * @return void
     */
    protected function parseGlobalUploadFiles()
    {
        $files = array();

        foreach ($_FILES['file'] as $param => $value) {
            if (!is_array($param)) {
                $files[$param][] = $value;

                continue;
            }

            $files[$param] = $value;
        }
        unset($_FILES['file']);

        $_FILES['files'] = $files;
    }

    /**
     * Get response information.
     *
     * @param array $uploads The uploads.
     *
     * @return array
     */
    protected function getResponse(array $uploads)
    {
        $content = '';
        $status  = 'error';

        if (count($uploads) > 0) {
            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $GLOBALS['container']['event-dispatcher'];

            $file = FilesModel::findByPath($uploads[0]);

            $table = Input::post('table');
            Controller::loadDataContainer($table);

            $event = new GetFileTreeWidgetEvent($eventDispatcher, $table, Input::get('dropfield'), $file);
            $eventDispatcher->dispatch(GetFileTreeWidgetEvent::NAME, $event);

            $content = $event->getWidget()->generate();

            $status = 'confirmed';
        }

        $message = Message::generate();
        Message::reset();

        return array('content' => $content, 'message' => $message, 'status' => $status);
    }
}
