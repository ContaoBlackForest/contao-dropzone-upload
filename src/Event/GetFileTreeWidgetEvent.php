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

use Contao\File;
use Contao\FilesModel;
use Contao\Widget;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Get file tree widget event.
 */
class GetFileTreeWidgetEvent extends Event
{
    /**
     * @var string The event name.
     */
    const NAME = 'ContaoBlackForest\DropZoneBundle\Event\GetFileTreeWidgetEvent';

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
     * @var FilesModel The upload file.
     */
    protected $uploadFile;

    /**
     * @var Widget The widget.
     */
    protected $widget;

    /**
     * GetUploadFolderEvent constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param string                   $dataProvider    The data provider.
     * @param string                   $property        The data property.
     * @param FilesModel               $uploadFile      The uploaded file.
     *
     * @internal param string $uploadFolder The upload folder.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $dataProvider, $property, $uploadFile)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataProvider    = $dataProvider;
        $this->property        = $property;
        $this->uploadFile      = $uploadFile;
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
     * @return FilesModel The upload folder.
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     */
    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;
    }
}
