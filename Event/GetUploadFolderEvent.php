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
 * Get upload folder event.
 */
class GetUploadFolderEvent extends Event
{
    /**
     * @var string The event name.
     */
    const NAME = 'ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent';

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
     * GetUploadFolderEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param string                   $dataProvider    The data provider.
     * @param string                   $property        The data property.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $dataProvider, $property)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataProvider    = $dataProvider;
        $this->property        = $property;
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
     * @param string $uploadFolder The upload folder.
     *
     * @return void
     */
    public function setUploadFolder($uploadFolder)
    {
        $this->uploadFolder = $uploadFolder;
    }
}
