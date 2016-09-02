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

    public function getDescription(GetDropZoneDescriptionEvent $event)
    {
        $event->setDescription(
            sprintf($GLOBALS['TL_LANG'][$event->getDataProvider()]['dropzone']['upload'], $event->getUploadFolder())
        );
    }
}
