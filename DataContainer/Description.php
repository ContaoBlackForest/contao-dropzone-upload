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

namespace ContaoBlackForest\DropZoneBundle\DataContainer;

use Contao\Config;
use Contao\System;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneDescriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The drop zone default description subscriber
 */
class Description implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            GetDropZoneDescriptionEvent::NAME => array(
                array('getDescription')
            )
        );
    }

    /**
     * Get the description for the drop zone.
     *
     * @param GetDropZoneDescriptionEvent $event The event.
     *
     * @return void
     */
    public function getDescription(GetDropZoneDescriptionEvent $event)
    {
        System::loadLanguageFile('tl_files');

        $event->setDescription(
            sprintf(
                '<strong>%s</strong> %s',
                sprintf(
                    $GLOBALS['TL_LANG']['MSC']['dropzone']['upload'],
                    $event->getUploadFolder()
                ),
                sprintf(
                    $GLOBALS['TL_LANG']['tl_files']['fileupload'][1],
                    System::getReadableSize($this->getMaximumUploadSize()),
                    Config::get('gdMaxImgWidth') . 'x' . Config::get('gdMaxImgHeight')
                )
            )
        );
    }

    /**
     * Return the maximum upload file size in bytes
     *
     * @return string
     */
    protected function getMaximumUploadSize()
    {
        // Get the upload_max_filesize from the php.ini
        $uploadMaxFileSize = ini_get('upload_max_filesize');

        // Convert the value to bytes
        if (stripos($uploadMaxFileSize, 'K') !== false) {
            $uploadMaxFileSize = round($uploadMaxFileSize * 1024);
        } elseif (stripos($uploadMaxFileSize, 'M') !== false) {
            $uploadMaxFilesize = round($uploadMaxFileSize * 1024 * 1024);
        } elseif (stripos($uploadMaxFileSize, 'G') !== false) {
            $uploadMaxFileSize = round($uploadMaxFileSize * 1024 * 1024 * 1024);
        }

        return min($uploadMaxFileSize, \Config::get('maxFileSize'));
    }
}
