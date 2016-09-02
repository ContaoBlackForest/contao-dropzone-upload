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
 * Get property table event for DC_TABLE data container.
 */
class GetPropertyTableEvent extends Event
{
    /**
     * @var string The event name.
     */
    const NAME = 'ContaoBlackForest\DropZoneBundle\Event\GetPropertyTableEvent';

    /**
     * @var EventDispatcherInterface The event dispatcher.
     */
    protected $eventDispatcher;

    /**
     * @var string The data provider.
     */
    protected $dataProvider;

    /**
     * @var string The property.
     */
    protected $property;

    /**
     * GetPropertyTableEvent constructor.
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

    /**
     * @return string The property.
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set the property
     *
     * @param string $property The property.
     *
     * @return void
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
}
