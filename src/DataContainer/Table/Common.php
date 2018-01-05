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
use Contao\Config;
use Contao\ContentModel;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use ContaoBlackForest\DropZoneBundle\Event\GetDropZoneUrlEvent;
use ContaoBlackForest\DropZoneBundle\Event\GetFilenameEvent;
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
            ),

            GetFilenameEvent::NAME => array(
                array('reviseFileName')
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
            $uploadFolder = $this->findUploadFolderByModule($event);
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
     * Revise the file name.
     *
     * @param GetFilenameEvent $event The event.
     *
     * @return void
     */
    public function reviseFileName(GetFilenameEvent $event)
    {
        if (!in_array(Input::get('do'), array('article', 'news', 'calendar', 'faq', 'newsletter'))) {
            return null;
        }

        $itemModel = null;
        if ('tl_content' === $event->getDataProvider()) {
            $contentModelClass = Model::getClassFromTable($event->getDataProvider());
            if (!class_exists($contentModelClass)) {
                return;
            }

            $contentModel = $contentModelClass::findByPk(Input::get('id'));
            if (!$contentModel || !$contentModel->pid || !$contentModel->ptable) {
                return;
            }

            $itemModelClass = Model::getClassFromTable($contentModel->ptable);
            if (!class_exists($itemModelClass)) {
                return;
            }

            $itemModel = $itemModelClass::findByPk($contentModel->pid);
        }

        if (!$itemModel) {
            $itemModelClass = Model::getClassFromTable($event->getDataProvider());
            if (!class_exists($itemModelClass)) {
                return;
            }

            $itemModel = $itemModelClass::findByPk(Input::get('id'));
        }

        if (!$itemModel || !$itemModel->pid) {
            return;
        }

        $parentModel = $itemModel->getRelated('pid');
        if (!$parentModel->dropzoneNotOverride) {
            if (5 !== $GLOBALS['TL_DCA'][$parentModel->getTable()]['list']['sorting']['mode']) {
                return;
            }

            // Support of inheritance in the tree view.
            if (!$parentModel->pid) {
                return;
            }

            $trailModel = $parentModel::findByPk($parentModel->pid);
            while ($trailModel->pid) {
                if ($trailModel->dropzoneNotOverride) {
                    break;
                }

                $trailModel = $trailModel::findByPk($trailModel->pid);
            }

            if (!$trailModel->dropzoneNotOverride) {
                return;
            }

            foreach (array('dropzoneNotOverride', 'dropzonePostfix', 'dropzoneCounterLength') as $trailProperty) {
                $parentModel->{$trailProperty} = $trailModel->{$trailProperty};
            }
        }

        $uploadFolder = Input::get('dropfolder');
        if (!file_exists(
            TL_ROOT . DIRECTORY_SEPARATOR . $uploadFolder . DIRECTORY_SEPARATOR . $event->getOriginalFilename()
        )) {
            return;
        }

        $filesModel = FilesModel::findMultipleFilesByFolder($uploadFolder);
        if (!$filesModel) {
            return;
        }

        $filename  = substr($event->getOriginalFilename(), 0, strrpos($event->getOriginalFilename(), '.'));
        $extension = substr($event->getOriginalFilename(), strrpos($event->getOriginalFilename(), '.'));

        $files = array();
        while ($filesModel->next()) {
            if (false === stripos($filesModel->name, $filename) && false === stripos($filesModel->name, $extension)) {
                continue;
            }

            $files[] = $filesModel->name;
        }
        sort($files);

        $counter     = $parentModel->dropzoneCounterLength;
        $newFilename = $parentModel->dropzonePostfix . $filename . '-' . $counter;

        while (in_array($newFilename . $extension, $files)) {
            $counter++;
            //Support leading 0 in the file counter.
            $counter = str_pad($counter, strlen($parentModel->dropzoneCounterLength), 0, STR_PAD_LEFT);

            $newFilename = $parentModel->dropzonePostfix . $filename . '-' . $counter;
        }

        if (!in_array($newFilename . $extension, $files)) {
            $event->setFilename($newFilename . $extension);

            return;
        }
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
     * Find the upload folder by core modules.
     *
     * @param GetUploadFolderEvent $event The event.
     *
     * @return string|null
     */
    private function findUploadFolderByModule(GetUploadFolderEvent $event)
    {
        if (!in_array(Input::get('do'), array('news', 'calendar', 'faq', 'newsletter'))) {
            return null;
        }

        $itemModel = null;
        if ('tl_content' === $event->getDataProvider()) {
            $contentModelClass = Model::getClassFromTable($event->getDataProvider());
            if (!class_exists($contentModelClass)) {
                return null;
            }

            $contentModel = $contentModelClass::findByPk(Input::get('id'));
            if (!$contentModel || !$contentModel->pid || !$contentModel->ptable) {
                return null;
            }

            $itemModelClass = Model::getClassFromTable($contentModel->ptable);
            if (!class_exists($itemModelClass)) {
                return null;
            }

            $itemModel = $itemModelClass::findByPk($contentModel->pid);
        }

        if (!$itemModel) {
            $itemModelClass = Model::getClassFromTable($event->getDataProvider());
            if (!class_exists($itemModelClass)) {
                return null;
            }

            $itemModel = $itemModelClass::findByPk(Input::get('id'));
        }

        if (!$itemModel || !$itemModel->pid) {
            return null;
        }

        $parentModel = $itemModel->getRelated('pid');

        if ($parentModel->dropzoneTitleInFolder) {
            $extendedPath = StringUtil::generateAlias($parentModel->title);
        }
        if ($parentModel->dropzoneAliasInFolder && $itemModel->alias) {
            $extendedPath = $extendedPath ? $extendedPath . '/' . $itemModel->alias : $itemModel->alias;
        }

        return $this->getUploadDirectoryFromModel($parentModel, null, $extendedPath);
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
