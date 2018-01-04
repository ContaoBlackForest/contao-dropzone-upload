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

namespace ContaoBlackForest\DropZoneBundle\DataContainer\Table;

use Contao\ArticleModel;
use Contao\BackendUser;
use Contao\CalendarEventsModel;
use Contao\Config;
use Contao\ContentModel;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\FaqModel;
use Contao\FilesModel;
use Contao\Input;
use Contao\Model;
use Contao\NewsletterModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
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

        $uploadFolder = $this->findPageUploadFolder($event);
        if (!$uploadFolder) {
            $uploadFolder = $this->findNewsSectionUploadFolder($event);
        }
        if (!$uploadFolder) {
            $uploadFolder = $this->findCalendarSectionUploadFolder($event);
        }
        if (!$uploadFolder) {
            $uploadFolder = $this->findFaqSectionUploadFolder($event);
        }
        if (!$uploadFolder) {
            $uploadFolder = $this->findNewsletterSectionUploadFolder($event);
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
     * @return string|null
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

        $uploadFolder = null;
        $pageModel->loadDetails();

        if (!$pageModel->dropzoneFolder) {

            if (count($pageModel->trail)) {
                foreach ($pageModel->trail as $pageId) {
                    $trailPageModel = PageModel::findByPk($pageId);
                    if (!$trailPageModel->dropzoneFolder) {
                        continue;
                    }

                    // Override the dropzone folder chunks in the trail page.
                    if ($pageModel->dropzoneExtendFolderPath) {
                        $trailPageModel->dropzoneFolderChunks = $pageModel->dropzoneFolderChunks;
                    }

                    $uploadFolder = $this->getUploadDirectoryFromModel($trailPageModel, $pageModel);
                    break;
                }
            }
        } else {
            $uploadFolder = $this->getUploadDirectoryFromModel($pageModel, $pageModel);
        }

        return $uploadFolder;
    }

    /**
     * Find the upload folder for the news section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return string|null
     */
    private function findNewsSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('news' !== Input::get('do')) {
            return null;
        }

        $extendedPath = null;

        if ('tl_news' === $event->getDataProvider()) {
            $newsModel = NewsModel::findByPk(Input::get('id'));
            if (!$newsModel) {
                return null;
            }

            $newsArchiveModel = $newsModel->getRelated('pid');

            if ($newsArchiveModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($newsArchiveModel->title);
            }
            if ($newsArchiveModel->dropzoneAliasInFolder) {
                $extendedPath = $extendedPath ? $extendedPath . '/' . $newsModel->alias : $newsModel->alias;
            }

            return $this->getUploadDirectoryFromModel($newsArchiveModel, null, $extendedPath);
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

            if ($newsArchiveModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($newsArchiveModel->title);
            }
            if ($newsArchiveModel->dropzoneAliasInFolder) {
                $extendedPath = $extendedPath ? $extendedPath . '/' . $newsModel->alias : $newsModel->alias;
            }

            return $this->getUploadDirectoryFromModel($newsArchiveModel, null, $extendedPath);
        }

        return null;
    }

    /**
     * Find the upload folder for the calendar section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return string|null
     */
    private function findCalendarSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('calendar' !== Input::get('do')) {
            return null;
        }

        $extendedPath = null;

        if ('tl_calendar_events' === $event->getDataProvider()) {
            $calendarEventsModel = CalendarEventsModel::findByPk(Input::get('id'));
            if (!$calendarEventsModel) {
                return null;
            }

            $calendarModel = $calendarEventsModel->getRelated('pid');

            if ($calendarModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($calendarModel->title);
            }
            if ($calendarModel->dropzoneAliasInFolder) {
                $extendedPath =
                    $extendedPath ? $extendedPath . '/' . $calendarEventsModel->alias : $calendarEventsModel->alias;
            }

            return $this->getUploadDirectoryFromModel($calendarModel, null, $extendedPath);
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

            if ($calendarModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($calendarModel->title);
            }
            if ($calendarModel->dropzoneAliasInFolder) {
                $extendedPath =
                    $extendedPath ? $extendedPath . '/' . $calendarEventsModel->alias : $calendarEventsModel->alias;
            }

            return $this->getUploadDirectoryFromModel($calendarModel, null, $extendedPath);
        }

        return null;
    }

    /**
     * Find the upload folder for the faq section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return string|null
     */
    private function findFaqSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('faq' !== Input::get('do')) {
            return null;
        }

        $extendedPath = null;

        if ('tl_faq' === $event->getDataProvider()) {
            $faqModel = FaqModel::findByPk(Input::get('id'));
            if (!$faqModel) {
                return null;
            }

            $faqCategoryModel = $faqModel->getRelated('pid');

            if ($faqCategoryModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($faqCategoryModel->title);
            }
            if ($faqCategoryModel->dropzoneAliasInFolder) {
                $extendedPath = $extendedPath ? $extendedPath . '/' . $faqModel->alias : $faqModel->alias;
            }

            return $this->getUploadDirectoryFromModel($faqCategoryModel, null, $extendedPath);
        }

        return null;
    }

    /**
     * Find the upload folder for the newsletter section.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return string|null
     */
    private function findNewsletterSectionUploadFolder(GetUploadFolderEvent $event)
    {
        if ('newsletter' !== Input::get('do')) {
            return null;
        }

        $extendedPath = null;

        if ('tl_newsletter' === $event->getDataProvider()) {
            $newsletterModel = NewsletterModel::findByPk(Input::get('id'));
            if (!$newsletterModel) {
                return null;
            }

            $newsletterChannelModel = $newsletterModel->getRelated('pid');

            if ($newsletterChannelModel->dropzoneTitleInFolder) {
                $extendedPath = StringUtil::generateAlias($newsletterChannelModel->title);
            }
            if ($newsletterChannelModel->dropzoneAliasInFolder) {
                $extendedPath = $extendedPath ? $extendedPath . '/' . $newsletterModel->alias : $newsletterModel->alias;
            }

            return $this->getUploadDirectoryFromModel($newsletterChannelModel, null, $extendedPath);
        }

        return null;
    }

    /**
     * Get the upload directory from the model.
     *
     * @param Model          $model        The model width the upload directory data.
     *
     * @param PageModel|null $pageModel    The page model for usage the available insert tags (optional).
     *
     * @param string|null    $extendedPath The extended path.
     *
     * @return string|null
     */
    private function getUploadDirectoryFromModel(Model $model, PageModel $pageModel = null, $extendedPath = null)
    {
        $filesModel = FilesModel::findByUuid($model->dropzoneFolder);
        if (!$filesModel) {
            return null;
        }
        $uploadFolder = $filesModel->path;
        if ($extendedPath) {
            $uploadFolder .= '/' . $extendedPath;
        }

        // Set the object page for support the page insert tags.
        if ($pageModel) {
            $GLOBALS['objPage'] = $pageModel;
        }

        if ($model->dropzoneExtendFolderPath) {
            foreach (unserialize($model->dropzoneFolderChunks) as $item) {
                $uploadFolder .= '/' . Controller::replaceInsertTags($item['chunk']);
            }
        }

        if ($pageModel) {
            unset($GLOBALS['objPage']);
        }

        return $uploadFolder;
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
