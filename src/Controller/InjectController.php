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

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\DataContainer;
use Contao\Environment;
use Contao\FileTree;
use Contao\Input;
use Contao\Widget;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneDescriptionEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneUrlEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Controller for inject the drop zone.
 */
class InjectController
{
    /**
     * Initialize parse widget hook.
     *
     * @param               $value mixed The widget value.
     *
     * @param DataContainer $dc    The data container.
     *
     * @return mixed The widget value.
     */
    public function initializeParseWidget($value, DataContainer $dc)
    {
        $GLOBALS['TL_HOOKS']['parseWidget'][$dc->table . '__' . $dc->field] = array(get_class($this), 'injectDropZone');

        return $value;
    }

    /**
     * Include drop zone assets.
     *
     * @return void
     */
    private function includeDropZoneAssets()
    {
        $css        = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/css/dropzone.min.css';
        $javascript = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/js/dropzone.min.js';

        if (!in_array('TL_CSS', $GLOBALS, null)
            || !in_array($css, $GLOBALS['TL_CSS'], null)
        ) {
            $GLOBALS['TL_CSS'][] = $css;
        }

        if (!in_array('TL_JAVASCRIPT', $GLOBALS, null)
            || !in_array($javascript, $GLOBALS['TL_JAVASCRIPT'], null)
        ) {
            $GLOBALS['TL_JAVASCRIPT'][] = $javascript;
        }

        Controller::loadLanguageFile('tl_files');
    }

    /**
     * Inject the drop zone.
     *
     * @param string $buffer The widget string.
     *
     * @param Widget $widget The widget.
     *
     * @return string
     */
    public function injectDropZone($buffer, Widget $widget)
    {
        // Unset the hook parse widget for this property.
        unset($GLOBALS['TL_HOOKS']['parseWidget'][$widget->strTable . '__' . $widget->name]);

        if (!$widget instanceof FileTree
            || !$widget->extensions
        ) {
            return $buffer;
        }

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];

        $uploadFolderEvent = new GetUploadFolderEvent($eventDispatcher, $widget->strTable, $widget->name);
        $eventDispatcher->dispatch(GetUploadFolderEvent::NAME, $uploadFolderEvent);
        $uploadFolder = $uploadFolderEvent->getUploadFolder();

        if (!$uploadFolder) {
            return $buffer;
        }

        // Manipulate the ajax request for get the right html structure from the widget.
        if (Environment::get('isAjaxRequest')) {
            $isAjaxRequest = Environment::get('isAjaxRequest');
            Environment::set('isAjaxRequest', false);

            $buffer = $widget->parse();

            Environment::set('isAjaxRequest', $isAjaxRequest);
        }

        $dropZoneUrlEvent = new GetDropZoneUrlEvent($eventDispatcher, $widget->strTable, $widget->name, $uploadFolder);
        $eventDispatcher->dispatch(GetDropZoneUrlEvent::NAME, $dropZoneUrlEvent);

        if (!$dropZoneUrlEvent->getUrl()) {
            return $buffer;
        }

        $dropZoneDescriptionEvent =
            new GetDropZoneDescriptionEvent($eventDispatcher, $widget->strTable, $widget->name, $uploadFolder);
        $eventDispatcher->dispatch(GetDropZoneDescriptionEvent::NAME, $dropZoneDescriptionEvent);

        $this->includeDropZoneAssets();

        $dropZone                    = new BackendTemplate('be_image_dropzone');
        $dropZone->url               = '\'' . $dropZoneUrlEvent->getUrl() . '\'';
        $dropZone->uploadDescription = $dropZoneDescriptionEvent->getDescription();
        $dropZone->controlInputField = $widget->id;
        $dropZone->dropzonePreviews  = 'dropzone_previews_' . $widget->name;
        $dropZone->multiple          = $widget->multiple ? 1 : 0;
        $dropZone->orderField        = $widget->orderField;
        $dropZone->extensions        = $widget->extensions;
        $dropZone->table             = $widget->strTable;

        if ('toggleSubpalette' === Input::post('action')) {
            $dropZone->stylesheet = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/css/dropzone.min.css';
            $dropZone->javascript = 'assets/dropzone/' . $GLOBALS['TL_ASSETS']['DROPZONE'] . '/js/dropzone.min.js';
        }

        return $buffer . $dropZone->parse();
    }
}
