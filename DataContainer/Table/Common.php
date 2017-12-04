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

use Contao\ArticleModel;
use Contao\BackendUser;
use Contao\CalendarEventsModel;
use Contao\Config;
use Contao\ContentModel;
use Contao\Database;
use Contao\Environment;
use Contao\FaqModel;
use Contao\FilesModel;
use Contao\Input;
use Contao\NewsModel;
use Contao\PageModel;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneUrlEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetUploadFolderEvent;
use ContaoBlackForest\DropZoneBundle\Event\InitializeDropZoneForPropertyEvent;
use Database\Result;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Data container table common subscriber.
 */
class Common implements EventSubscriberInterface
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
            InitializeDropZoneForPropertyEvent::NAME => array(
                array('initialize')
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
     * Initialize the dropzone for all properties that has the widget of fileTree for the datacontainer table.
     *
     * @param InitializeDropZoneForPropertyEvent $event The event.
     *
     * @return void
     */
    public function initialize(InitializeDropZoneForPropertyEvent $event)
    {
        if (!$this->hasBackendUserUploaderDropZone()
            || !isset($GLOBALS['TL_DCA'][$event->getDataProvider()])
            || 'Table' !== $GLOBALS['TL_DCA'][$event->getDataProvider()]['config']['dataContainer']
        ) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$event->getDataProvider()]['fields'] as $propertyName => $propertyConfig) {
            if (!isset($propertyConfig['inputType'])
                || ('fileTree' !== $propertyConfig['inputType'])
            ) {
                continue;
            }

            $this->addDropZoneToProperty($event->getDataProvider(), $propertyName);
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
        if (!isset($GLOBALS['TL_DCA'][$event->getDataProvider()])
            || ('Table' !== $GLOBALS['TL_DCA'][$event->getDataProvider()]['config']['dataContainer'])
        ) {
            return;
        }

        $folderUuid = $this->findPageUploadFolder($event);
        if (!$folderUuid) {
            $folderUuid = $this->findNewsSectionUploadFolder($event);
        }
        if (!$folderUuid) {
            $folderUuid = $this->findCalendarSectionUploadFolder($event);
        }
        if (!$folderUuid) {
            $folderUuid = $this->findFaqSectionUploadFolder($event);
        }

        if ($folderUuid) {
            $filesModel = FilesModel::findByUuid($folderUuid);
            if ($filesModel) {
                $uploadFolder = $filesModel->path;
            }
        }

        if (!$uploadFolder) {
            return;
        }

        $event->setUploadFolder($uploadFolder);
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
        if (!isset($GLOBALS['TL_DCA'][$event->getDataProvider()])
            || ('Table' !== $GLOBALS['TL_DCA'][$event->getDataProvider()]['config']['dataContainer'])
        ) {
            return;
        }

        $event->setUrl(
            Environment::get('request') .
            '&dropfield=' . $event->getProperty() .
            '&dropfolder=' . $event->getUploadFolder()
        );
    }

    /**
     * Find the uploader page upload folder.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return mixed|null
     */
    private function findPageUploadFolder(GetUploadFolderEvent $event)
    {
        if (('tl_content' !== $event->getDataProvider())
            || ('tl_article' !== $GLOBALS['TL_DCA'][$event->getDataProvider()]['config']['ptable'])
        ) {
            return null;
        }

        $contentModel = ContentModel::findByPk(Input::get('id'));
        $articleModel = ArticleModel::findByPk($contentModel->pid);
        $pageModel    = $articleModel->getRelated('pid');

        $folderUuid = null;
        if (!$pageModel->dropzoneFolder) {
            $pageModel->loadDetails();

            if (count($pageModel->trail)) {
                foreach ($pageModel->trail as $pageId) {
                    $trailPageModel = PageModel::findByPk($pageId);
                    if (!$trailPageModel->dropzoneFolder) {
                        continue;
                    }

                    $folderUuid = $trailPageModel->dropzoneFolder;
                }
            }
        } else {
            $folderUuid = $pageModel->dropzoneFolder;
        }

        return $folderUuid;
    }

    /**
     * Find the upload folder for the news section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return mixed|null
     */
    private function findNewsSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('news' !== Input::get('do')) {
            return null;
        }

        if ('tl_news' === $event->getDataProvider()) {
            $newsModel = NewsModel::findByPk(Input::get('id'));
            if (!$newsModel) {
                return null;
            }

            $newsArchiveModel = $newsModel->getRelated('pid');

            return $newsArchiveModel->dropzoneFolder;
        }

        if ('tl_content' === $event->getDataProvider()) {
            $contentModel = ContentModel::findByPk(Input::get('id'));
            if (!$contentModel) {
                return null;
            }

            $newsModel = NewsModel::findByPk($contentModel->pid);
            if (!$newsModel) {
                return null;
            }

            $newsArchiveModel = $newsModel->getRelated('pid');

            return $newsArchiveModel->dropzoneFolder;
        }

        return null;
    }

    /**
     * Find the upload folder for the calendar section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return mixed|null
     */
    private function findCalendarSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('calendar' !== Input::get('do')) {
            return null;
        }

        if ('tl_calendar_events' === $event->getDataProvider()) {
            $calendarEventsModel = CalendarEventsModel::findByPk(Input::get('id'));
            if (!$calendarEventsModel) {
                return null;
            }

            $calendarModel = $calendarEventsModel->getRelated('pid');

            return $calendarModel->dropzoneFolder;
        }

        if ('tl_content' === $event->getDataProvider()) {
            $contentModel = ContentModel::findByPk(Input::get('id'));
            if (!$contentModel) {
                return null;
            }

            $calendarEventsModel = CalendarEventsModel::findByPk($contentModel->pid);
            if (!$calendarEventsModel) {
                return null;
            }

            $calendarModel = $calendarEventsModel->getRelated('pid');

            return $calendarModel->dropzoneFolder;
        }

        return null;
    }

    /**
     * Find the upload folder for the faq section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return mixed|null
     */
    private function findFaqSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('faq' !== Input::get('do')) {
            return null;
        }

        if ('tl_faq' === $event->getDataProvider()) {
            $faqModel = FaqModel::findByPk(Input::get('id'));
            if (!$faqModel) {
                return null;
            }

            $faqCategoryModel = $faqModel->getRelated('pid');

            return $faqCategoryModel->dropzoneFolder;
        }

        return null;
    }

    /**
     * Has backend user configure uploader drop zone.
     *
     * @return bool
     */
    private function hasBackendUserUploaderDropZone()
    {
        $user = BackendUser::getInstance();

        return $user->uploader === 'DropZone';
    }

    /**
     * Add the dropzone to the property.
     *
     * @param string $dataProvider The data provider name.
     *
     * @param string $propertyName The propery name.
     *
     * @return void
     */
    private function addDropZoneToProperty($dataProvider, $propertyName)
    {
        if (!$this->isPropertyActive($dataProvider, $propertyName)) {
            return;
        }

        $GLOBALS['TL_DCA'][$dataProvider]['fields'][$propertyName]['load_callback'][] = array(
            'ContaoBlackForest\DropZoneBundle\Controller\InjectController',
            'initializeParseWidget'
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

        if ('toggleSubpalette' === Input::post('action')) {
            $result->{Input::post('field')} = Input::post('state');
        }

        if (array_key_exists($result->type, $GLOBALS['TL_DCA'][$dataProvider]['palettes'])) {
            $activePalette = explode(',', $GLOBALS['TL_DCA'][$dataProvider]['palettes'][$result->type]);
        } else {
            $activePalette = explode(',', $GLOBALS['TL_DCA'][$dataProvider]['palettes']['default']);
        }

        $activePaletteProperties = $this->getPaletteProperties($activePalette);


        if (in_array($property, $activePaletteProperties)) {
            return true;
        }

        if ($this->findPropertyInSubPalette($property, $result, $dataProvider, $activePaletteProperties)) {
            if (!isset($GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['eval']['extensions'])) {
                if (isset($GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['eval']['filesOnly'])
                    && $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['eval']['filesOnly']
                ) {
                    $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property]['eval']['extensions'] =
                        Config::get('uploadTypes');
                }
            }

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
        if (!isset($GLOBALS['TL_DCA'][$dataProvider]['subpalettes'])) {
            return true;
        }

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
