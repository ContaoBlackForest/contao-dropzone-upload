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

/**
 * Contao hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = array(
    'ContaoBlackForest\DropZoneBundle\Data\Store',
    'parse'
);
