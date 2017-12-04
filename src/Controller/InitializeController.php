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

use ContaoBlackForest\DropZoneBundle\Event\InitializeDropZoneForPropertyEvent;
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
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $GLOBALS['container']['event-dispatcher'];

        $event = new InitializeDropZoneForPropertyEvent($eventDispatcher, $dataProvider);
        $eventDispatcher->dispatch(InitializeDropZoneForPropertyEvent::NAME, $event);
    }
}
