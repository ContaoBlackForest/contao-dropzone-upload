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
 * Get drop zone url event.
 */
class GetDropZoneUrlEvent extends Event
{
    /**
     * @var string The event name.
     */
    const NAME = 'ContaoBlackForest\DropZoneBundle\Event\GetDropZoneUrlEvent';

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
     * @var string The upload folder.
     */
    protected $uploadFolder;

    /**
     * @var string The url.
     */
    protected $url;

    /**
     * GetDropZoneUrlEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param string                   $dataProvider    The data provider.
     * @param string                   $property        The data property.
     * @param string                   $uploadFolder    The upload folder.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $dataProvider, $property, $uploadFolder)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataProvider    = $dataProvider;
        $this->property        = $property;
        $this->uploadFolder    = $uploadFolder;
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
     * @return string The upload folder.
     */
    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }

    /**
     * @return string The url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url The url.
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
