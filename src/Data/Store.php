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

use Contao\DataContainer;
use Contao\Dbafs;
use Contao\DC_Table;
use Contao\FilesModel;
use Contao\FileUpload;
use Contao\Input;
use Contao\Model;
use Contao\Module;

class Store
{
    public function parse(DC_Table $dc)
    {
        if (!Input::get('dropfield')
            || !Input::get('dropfolder')
        ) {
            return;
        }

        $this->parseFiles();

        $upload  = new FileUpload();
        $uploads = $upload->uploadTo(Input::get('dropfolder'));
        if (empty($uploads)) {
            // Todo send error
            return;
        }

        $modelClass = Model::getClassFromTable(Input::get('table'));
        $result     = $modelClass::findOneById(Input::get('id'));

        Dbafs::syncFiles();

        $this->storeSingleSource($result, $uploads[0]);
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
