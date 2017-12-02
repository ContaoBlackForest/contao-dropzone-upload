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

namespace ContaoBlackForest\DropZoneBundle\Controller;

use Contao\Controller;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\RequestToken;
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

        $upload  = new FileUpload();
        $uploads = $upload->uploadTo(Input::get('dropfolder'));

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
            global $container;

            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $container['event-dispatcher'];

            $file = FilesModel::findByPath($uploads[0]);

            $table = Input::get('table');
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
