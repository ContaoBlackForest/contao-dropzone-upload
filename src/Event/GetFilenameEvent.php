<?php

/**
 * Copyright © ContaoBlackForest
 *
 * @package   contao-dropzone-upload
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2014-2018 ContaoBlackForest
 */

namespace ContaoBlackForest\DropZoneBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Get the file name event.
 */
class GetFilenameEvent extends Event
{
    /**
     * The event name.
     *
     * @var string
     */
    const NAME = 'cb.dropzone_upload.get_filename';

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The data provider.
     *
     * @var string
     */
    protected $dataProvider;

    /**
     * The original file name.
     *
     * @var string
     */
    protected $originalFilename;

    /**
     * The file name.
     *
     * @var string.
     */
    protected $filename;

    public function __construct(EventDispatcherInterface $eventDispatcher, $dataProvider, $filename)
    {
        $this->eventDispatcher  = $eventDispatcher;
        $this->dataProvider     = $dataProvider;
        $this->originalFilename = $filename;
        $this->filename         = $filename;
    }

    /**
     * Get the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Get the data provider.
     *
     * @return string
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * Get the original file name.
     *
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * Get the file name.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the file name.
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
