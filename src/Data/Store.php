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

namespace ContaoBlackForest\DropZone\Data;

use Contao\Controller;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\DC_Table;
use Contao\FilesModel;
use Contao\FileTree;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\Model;
use Contao\Module;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Image;

class Store
{
    public function parse($action)
    {
        if ($action !== 'dropZoneAjax'
            || !Input::get('dropfield')
            || !Input::get('dropfolder')
            || RequestToken::validate(Input::get('ref'))
        ) {
            return;
        }

        $this->parseFiles();

        $upload  = new FileUpload();
        $uploads = $upload->uploadTo(Input::get('dropfolder'));

        //$modelClass = Model::getClassFromTable(Input::get('table'));
        //$result     = $modelClass::findOneById(Input::get('id'));

        Dbafs::syncFiles();

        //$this->storeSingleSource($result, $uploads[0]);

        header('Content-Type: application/json');
        echo json_encode($this->getResponse($uploads));
        exit;
    }

    protected function parseFiles()
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

    protected function getResponse(array $uploads)
    {
        $content = '';
        $status = 'error';

        if (count($uploads) > 0) {
            $file = FilesModel::findByPath($uploads[0]);

            $table = Input::get('table');
            Controller::loadDataContainer($table);
            $dc = new DC_Table($table);
            $dc->__set('strField', Input::get('dropfield'));

            $widget = new FileTree(FileTree::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field], $dc->field, serialize(array($file->uuid)), Input::get('dropfield'), $dc->table, $dc));
            $content = $widget->generate();

            $status = 'confirmed';
        }

        $message = Message::generate();
        Message::reset();

        return array('content' => $content, 'message' => $message, 'status' => $status);
    }

    protected function storeSingleSource($model, $file)
    {
        if (Input::get('dropfield') !== 'singleSRC') {
            return;
        }

        $fileModel = FilesModel::findMultipleFilesByFolder(Input::get('dropfolder'));
        if (!$fileModel) {
            return;
        }

        while ($fileModel->next()) {
            if ($fileModel->path !== $file) {
                continue;
            }

            $model->singleSRC = $fileModel->uuid;
            $model->save();
        }
    }
}
