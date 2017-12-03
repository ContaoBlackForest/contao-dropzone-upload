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
use Contao\FileTree;
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
     * @param $value mixed The widget value.
     *
     * @return mixed The widget value.
     */
    public function initializeParseWidget($value)
    {
        $GLOBALS['TL_HOOKS']['parseWidget'][] = array(get_class($this), 'injectDropZone');

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
     * @param Widget $widget The widget.
     *
     * @return string
     */
    public function injectDropZone($buffer, Widget $widget)
    {
        if (!$widget instanceof FileTree) {
            return $buffer;
        }

        global $container;

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];

        $uploadFolderEvent = new GetUploadFolderEvent($eventDispatcher, $widget->strTable, $widget->name);
        $eventDispatcher->dispatch(GetUploadFolderEvent::NAME, $uploadFolderEvent);
        $uploadFolder = $uploadFolderEvent->getUploadFolder();

        if (!$uploadFolder) {
            return $buffer;
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

        return $buffer . $dropZone->parse();
    }
}
