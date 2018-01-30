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

namespace ContaoBlackForest\DropZoneBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Initialize the dropzone for property event.
 */
class InitializeDropZoneForPropertyEvent extends Event
{
    /**
     * @var string The event name.
     */
    const NAME = 'cb.dropzone_upload.initialize_for_property';

    /**
     * @var EventDispatcherInterface The event dispatcher.
     */
    protected $eventDispatcher;

    /**
     * @var string The data provider.
     */
    protected $dataProvider;

    /**
     * InitializeDropZoneForPropertyEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param string                   $dataProvider    The data provider.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $dataProvider)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataProvider    = $dataProvider;
    }

    /**
     * @return EventDispatcherInterface The event dispatcher.
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string The data provider.
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
}
