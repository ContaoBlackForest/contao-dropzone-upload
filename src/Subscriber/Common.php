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

namespace ContaoBlackForest\DropZoneBundle\Subscriber;

use ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The common subscriber for default settings.
 */
class Common implements EventSubscriberInterface
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
            GetUploadFolderEvent::NAME => array(
                array('getUploadFolder', -50000)
            )
        );
    }

    /**
     * Get upload folder.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return void
     */
    public function getUploadFolder(GetUploadFolderEvent $event)
    {
        if ($event->getUploadFolder()) {
            return;
        }

        $event->setUploadFolder('files/dropzone_upload');
    }
}
