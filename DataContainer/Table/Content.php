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
use Contao\Environment;
use Contao\Input;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneUrlEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetPropertyTableEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent;
use Database\Result;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Data container table content subscriber.
 */
class Content implements EventSubscriberInterface
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
            GetPropertyTableEvent::NAME => array(
                array('InitializeTableForPropertySingleSource'),
                array('InitializeTableForPropertyMultiSource')
            ),

            GetUploadFolderEvent::NAME => array(
                array('getUploadFolder')
            ),

            GetDropZoneUrlEvent::NAME => array(
                array('getDropZoneUrl')
            )
        );
    }

    /**
     * Initialize for table tl_content and the property single source.
     *
     * @param GetPropertyTableEvent $event The event.
     *
     * @return void
     */
    public function InitializeTableForPropertySingleSource(GetPropertyTableEvent $event)
    {
        $dataProvider = $event->getDataProvider();

        if ($dataProvider !== 'tl_content'
            || !array_key_exists('singleSRC', $GLOBALS['TL_DCA'][$dataProvider]['fields'])
            || $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] !== 'Table'
        ) {
            return;
        }

        if ($this->isPropertyActive($dataProvider, 'singleSRC')) {
            $event->setProperty('singleSRC');
        }
    }

    /**
     * Initialize for table tl_content and the property multi source.
     *
     * @param GetPropertyTableEvent $event The event.
     *
     * @return void
     */
    public function InitializeTableForPropertyMultiSource(GetPropertyTableEvent $event)
    {
        $dataProvider = $event->getDataProvider();

        if ($dataProvider !== 'tl_content'
            || !array_key_exists('multiSRC', $GLOBALS['TL_DCA'][$dataProvider]['fields'])
            || $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] !== 'Table'
        ) {
            return;
        }

        if ($this->isPropertyActive($dataProvider, 'multiSRC')) {
            $event->setProperty('multiSRC');
        }
    }

    /**
     * Get upload folder.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return void
     */
    public function getUploadFolder(GetUploadFolderEvent $event)
    {
        if ($event->getDataProvider() !== 'tl_content') {
            return;
        }

        $event->setUploadFolder('files/tiny_templates');
    }

    /**
     * Get drop zone url.
     *
     * @param GetDropZoneUrlEvent $event The event.
     *
     * @return void
     */
    public function getDropZoneUrl(GetDropZoneUrlEvent $event)
    {
        if ($event->getDataProvider() !== 'tl_content') {
            return;
        }

        $event->setUrl(
            Environment::get('request') .
            '&dropfield=' . $event->getProperty() .
            '&dropfolder=' . $event->getUploadFolder()
        );
    }

    /**
     * Check if the property is active in palette.
     *
     * @param string $dataProvider The data provider.
     *
     * @param string $property     The property.
     *
     * @return bool
     */
    private function isPropertyActive($dataProvider, $property)
    {
        $database = Database::getInstance();
        $result   = $database->prepare("SELECT * FROM $dataProvider WHERE id=?")
            ->execute(Input::get('id'));

        $activePalette           = explode(',', $GLOBALS['TL_DCA'][$dataProvider]['palettes'][$result->type]);
        $activePaletteProperties = $this->getPaletteProperties($activePalette);


        if (in_array($property, $activePaletteProperties)) {
            return true;
        }

        if ($this->findPropertyInSubPalette($property, $result, $dataProvider, $activePaletteProperties)) {
            return true;
        }

        return false;
    }

    /**
     * Get the palette properties.
     *
     * @param array $palette The palette.
     *
     * @return array
     */
    private function getPaletteProperties(array $palette)
    {
        $paletteProperties = array();

        foreach ($palette as $paletteProperty) {
            $paletteProperty = explode(';', $paletteProperty);
            if (strpos($paletteProperty[0], '_legend}') !== false) {
                continue;
            }

            $paletteProperties[] = $paletteProperty[0];
        }

        return $paletteProperties;
    }

    /**
     * Find the property in a sub palette.
     *
     * @param string $property          The property.
     *
     * @param Result $result            The database result.
     *
     * @param string $dataProvider      The data provider.
     *
     * @param array  $paletteProperties The palette properties.
     *
     * @return bool
     */
    private function findPropertyInSubPalette($property, Result $result, $dataProvider, array $paletteProperties)
    {
        foreach ($GLOBALS['TL_DCA'][$dataProvider]['subpalettes'] as $selector => $subPalette) {
            $subPalette = explode(',', $subPalette);

            if ($result->{$selector}
                && in_array($property, $subPalette)
                && in_array($selector, $paletteProperties)
            ) {
                return true;
            }
        }

        return false;
    }
}
