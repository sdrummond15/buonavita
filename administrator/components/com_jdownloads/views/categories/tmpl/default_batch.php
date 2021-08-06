<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// no direct access
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
$basePath  = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';

?> 

<div class="container-fluid">

    <div class="alert alert-info">
        <ul>
            <?php echo JText::_('COM_JDOWNLOADS_BATCH_FOLDER_NOTE'); ?>
            <?php echo JText::_('COM_JDOWNLOADS_BATCH_DESC'); ?>
        </ul>
    </div>

    <div class="row-fluid">
        <div class="control-group span6">
            <div class="controls">
                <?php echo JLayoutHelper::render('joomla.html.batch.language', array()); ?>
            </div>
        </div>
        <div class="control-group span6">
            <div class="controls">
                <?php echo JLayoutHelper::render('joomla.html.batch.access', array()); ?>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <?php if ($published >= 0) : ?>
            <div class="control-group span6">
                <div class="controls">
                  <?php // display category list box ?>
                  <?php echo JLayoutHelper::render('html.batch.categories', $this->categories, $basePath); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="control-group span6">
            <div class="controls">
                <?php echo JLayoutHelper::render('joomla.html.batch.tag', array()); ?>
            </div>
        </div>
    </div>
</div>