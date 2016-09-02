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

use ContaoBlackForest\DropZoneBundle\Event\GetPropertyTableEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Initialize drop zone controller.
 */
class InitializeController
{
    /**
     * Initialize property load callback.
     *
     * @param $dataProvider string The data provider.
     *
     * @return void
     */
    public function initializePropertyLoadCallback($dataProvider)
    {
        global $container;

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];

        $event = new GetPropertyTableEvent($eventDispatcher, $dataProvider);
        $eventDispatcher->dispatch(GetPropertyTableEvent::NAME, $event);

        if (!$event->getProperty()) {
            return;
        }

        $this->initializeWidgetLoadCallback($event);
    }

    /**
     * Initialize widget load callback for property.
     *
     * @param GetPropertyTableEvent $event The event.
     *
     * @return void
     */
    protected function initializeWidgetLoadCallback(GetPropertyTableEvent $event)
    {
        if (!$this->hasBackendUserUploaderDropZone()
            || !array_key_exists($event->getDataProvider(), $GLOBALS['TL_DCA'])
            || !array_key_exists($event->getProperty(), $GLOBALS['TL_DCA'][$event->getDataProvider()]['fields'])
        ) {
            return;
        }

        $GLOBALS['TL_DCA'][$event->getDataProvider()]['fields'][$event->getProperty()]['load_callback'][] = array(
            'ContaoBlackForest\DropZoneBundle\Controller\ContentSingleSourceController',
            'initializeParseWidget'
        );
    }

    /**
     * Has backend user configure uploader drop zone.
     *
     * @return bool
     */
    protected function hasBackendUserUploaderDropZone()
    {
        global $controller;

        $user = $controller->User;

        return $user->uploader === 'DropZone';
    }
}
