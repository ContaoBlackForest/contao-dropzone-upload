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

namespace ContaoBlackForest\DropZone\Controller;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Environment;
use Contao\FileTree;

abstract class AbstractController
{
    protected $parseWidget;

    protected $folder;

    protected $uploadMultiple = 'true';

    protected $maxFiles = 'null';

    public function initializeParseWidget($value, $dc)
    {
        $GLOBALS['TL_HOOKS']['parseWidget'][$this->parseWidget] = array(get_class($this), 'injectDropZone');

        return $value;
    }

    public function injectDropZone($buffer, FileTree $widget)
    {
        unset($GLOBALS['TL_HOOKS']['parseWidget'][$this->parseWidget]);

        $this->includeDropZoneAssets();

        $dropZone = new BackendTemplate('be_image_dropzone');
        $dropZone->setData(
            array(
                'url'            => '\'' .Environment::get('request') . '&dropfield=' . $widget->name . '&dropfolder=' . $this->folder. '\'',
                'maxFiles'    => $this->maxFiles,
                'uploadMultiple' => $this->uploadMultiple,
                'uploadDescription' => sprintf($GLOBALS['TL_LANG']['tl_content']['dropzone']['upload'], $this->folder),
                'controlInputField' => explode('.', $this->parseWidget)[1]
            )
        );

        $buffer .= $dropZone->parse();

        return $buffer;
    }

    protected function includeDropZoneAssets()
    {
        $GLOBALS['TL_CSS'][]        = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/css/dropzone.min.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/js/dropzone.min.js';

        Controller::loadLanguageFile('tl_files');
    }
}
