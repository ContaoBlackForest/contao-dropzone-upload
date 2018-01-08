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

use Contao\Controller;
use Contao\Database;
use Contao\FileTree;
use Contao\Input;
use Contao\System;
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

        if (!$GLOBALS['loadDataContainer'][$dataProvider]) {
            Controller::loadDataContainer($dataProvider);
        }
        if (false === isset($GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'])) {
            return;
        }

        $dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'];

        $property = $event->getProperty();

        $database = Database::getInstance();
        $result   = $database->prepare("SELECT * FROM $dataProvider WHERE id=?")
            ->limit(1)
            ->execute(Input::get('id'));

        $dc               = new $dataContainer($dataProvider);
        $dc->activeRecord = $result;
        $dc->field        = $property;

        if ((true === isset($GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['load_callback']))
            && (true === (bool) count($GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['load_callback']))
        ) {
            foreach ($GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['load_callback'] as $callback) {
                if (is_array($callback)) {
                    $reflectionClass = new \ReflectionClass($callback[0]);
                    $instance        = $reflectionClass->newInstance();

                    $instance->{$callback[1]}($result->{$property}, $dc);
                } elseif (is_callable($callback)) {
                    $callback($result->{$property}, $dc);
                }
            }
        }

        $widget =
            new FileTree(
                FileTree::getAttributesFromDca(
                    $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property],
                    $property,
                    '',
                    $property,
                    $dataProvider,
                    $dc
                )
            );

        if ((true === $widget->multiple)
            && (true === (bool) $widget->orderField)
        ) {
            $value = array_map('Contao\StringUtil::uuidToBin', (array) explode(',', Input::post('fieldValue')));
            if (!in_array($event->getUploadFile()->uuid, $value)) {
                $value = array_merge($value, array($event->getUploadFile()->uuid));
            }
        } else {
            $value = $event->getUploadFile()->uuid;
        }

        $widget->value = $value;

        if ((true === Input::post('multiple'))
            && (true === (bool) Input::post('orderValue'))
        ) {
            $orderValue = array_map('Contao\StringUtil::uuidToBin', (array) explode(',', Input::post('orderValue')));
            if (!in_array($event->getUploadFile()->uuid, $orderValue)) {
                $orderValue = array_merge($orderValue, array($event->getUploadFile()->uuid));
            }

            $widget->{$widget->orderField} = $orderValue;
        }

        $event->setWidget($widget);
    }
}
