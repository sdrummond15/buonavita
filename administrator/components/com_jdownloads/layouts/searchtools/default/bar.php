<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * Modified for jDownloads 
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
?>

<?php if ($data['view'] instanceof JDownloadsViewAssociations) : ?>
    <?php $app = JFactory::getApplication(); ?>
    <?php // We will get the component item type and language filters & remove it from the form filters. ?>
    <?php if ($app->input->get('forcedItemType', '', 'string') == '') : ?>
        <?php $itemTypeField = $data['view']->filterForm->getField('itemtype'); ?>
        <div class="js-stools-field-filter js-stools-selector">
            <?php echo $itemTypeField->input; ?>
        </div>
    <?php endif; ?>
    <?php if ($app->input->get('forcedLanguage', '', 'cmd') == '') : ?>
        <?php $languageField = $data['view']->filterForm->getField('language'); ?>
        <div class="js-stools-field-filter js-stools-selector">
            <?php echo $languageField->input; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php // Display the main joomla layout ?>
<?php if (!empty($filters['filter_search'])) : ?>
	<?php if ($searchButton) : ?>
		<label for="filter_search" class="element-invisible">
			<?php if (isset($filters['filter_search']->label)) : ?>
				<?php echo JText::_($filters['filter_search']->label); ?>
			<?php else : ?>
			<?php echo JText::_('JSEARCH_FILTER'); ?>
			<?php endif; ?>
		</label>
		<div class="btn-wrapper input-append">
			<?php echo $filters['filter_search']->input; ?>
			<?php if ($filters['filter_search']->description) : ?>
				<?php JHtmlBootstrap::tooltip('#filter_search', array('title' => JText::_($filters['filter_search']->description))); ?>
			<?php endif; ?>
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>" aria-label="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
				<span class="icon-search" aria-hidden="true"></span>
			</button>
		</div>
		<?php if ($filterButton) : ?>
			<div class="btn-wrapper hidden-phone">
				<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_TOOLS_DESC'); ?>">
					<?php echo JText::_('JSEARCH_TOOLS');?> <span class="caret"></span>
				</button>
			</div>
		<?php endif; ?>
		<div class="btn-wrapper">
			<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>
			</button>
		</div>
	<?php endif; ?>
<?php endif;
