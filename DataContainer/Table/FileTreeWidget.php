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

namespace ContaoBlackForest\DropZoneBundle\DataContainer\Table;

use Contao\Database;
use Contao\FileTree;
use Contao\Input;
use ContaoBlackForest\DropZoneBundle\Event\GetFileTreeWidgetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * File tree widget subscriber for data container DC_TABLE.
 */
class FileTreeWidget implements EventSubscriberInterface
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
            GetFileTreeWidgetEvent::NAME => array(
                array('getFileTreeWidget')
            )
        );
    }

    /**
     * Get file tree widget for data container DC_TABLE.
     *
     * @param GetFileTreeWidgetEvent $event
     *
     * @return void
     */
    public function getFileTreeWidget(GetFileTreeWidgetEvent $event)
    {
        $dataProvider = $event->getDataProvider();

        if (false === isset($GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'])) {
            return;
        }

        $dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'];

        $property = $event->getProperty();

        $database = Database::getInstance();
        $result   = $database->prepare("SELECT * FROM $dataProvider WHERE id=?")
            ->execute(Input::get('id'));

        $dc               = new $dataContainer($dataProvider);
        $dc->activeRecord = $result;
        $dc->field        = $property;


        $value = serialize(array($event->getUploadFile()->uuid));

        $widget =
            new FileTree(
                FileTree::getAttributesFromDca(
                    $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property],
                    $property,
                    $value,
                    $property,
                    $dataProvider,
                    $dc
                )
            );

        if (array_key_exists('eval', $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property])
            && array_key_exists('orderField', $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['eval'])
        ) {
            $database = Database::getInstance();
            $result = $database->prepare("SELECT * FROM $dataProvider WHERE id=?")
                ->execute(Input::get('id'));

            $widget->value = serialize(
                array_merge(
                    unserialize($result->$property),
                    array($event->getUploadFile()->uuid)
                )
            );

            switch ($result->type) {
                case 'gallery':
                    $widget->isGallery = true;
                    break;

                case  'download':
                    $widget->isDownloads = true;
                    break;

                default:
                    break;
            }
        }

        $event->setWidget($widget);
    }
}
