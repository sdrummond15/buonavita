<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('JDAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/associationshelper.php');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$canManageCheckin = JFactory::getUser()->authorise('core.manage', 'com_checkin');
$colSpan          = 5;

// Path to the layouts folder 
$basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
$options['base_path'] = $basePath;

$iconStates = array(
	0  => 'icon-unpublish',
	1  => 'icon-publish',
);

JText::script('COM_JDOWNLOADS_ASSOCIATIONS_PURGE_CONFIRM_PROMPT');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(pressbutton)
	{
		if (pressbutton == "associations.purge")
		{
			if (confirm(Joomla.JText._("COM_JDOWNLOADS_ASSOCIATIONS_PURGE_CONFIRM_PROMPT")))
			{
				Joomla.submitform(pressbutton);
			}
			else
			{
				return false;
			}
		}
		else
		{
			Joomla.submitform(pressbutton);
		}
	};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=associations'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php echo JLayoutHelper::render('searchtools.default', array('view' => $this), $basePath, $options); ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="associationsList">
			<thead>
				<tr>
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<th width="1%" class="center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap">
						<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JText::_('COM_JDOWNLOADS_HEADING_ASSOCIATION'); ?>
					</th>
					<th width="15%" class="nowrap">
						<?php echo JText::_('COM_JDOWNLOADS_HEADING_NO_ASSOCIATION'); ?>
					</th>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<?php if (!empty($this->typeFields['access'])) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $colSpan; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canEdit    = JDAssociationsHelper::allowEdit($this->extensionName, $this->typeName, $item->id);
				$canCheckin = $canManageCheckin || JDAssociationsHelper::canCheckinItem($this->extensionName, $this->typeName, $item->id);
				$isCheckout = JDAssociationsHelper::isCheckoutItem($this->extensionName, $this->typeName, $item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>"></span>
						</td>
					<?php endif; ?>
					<td class="has-context">
						<div class="pull-left break-word">
							<span style="display: none"><?php echo JHtml::_('grid.id', $i, $item->id); ?></span>
							<?php if (isset($item->level)) : ?>
								<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
							<?php endif; ?>
							<?php if (!$canCheckin && $isCheckout) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'associations.'); ?>
							<?php endif; ?>
							<?php if ($canCheckin && $isCheckout) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'associations.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit && !$isCheckout) : ?>
								<a href="<?php echo JRoute::_($this->editUri . '&id=' . (int) $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
							<?php endif; ?>
							<?php if (!empty($this->typeFields['alias'])) : ?>
								<span class="small">
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								</span>
							<?php endif; ?>
							<?php if (!empty($this->typeFields['catid'])) : ?>
								<div class="small">
									<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
								</div>
							<?php endif; ?>
						</div>
					</td>
					<td class="small">
						<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
					</td>
					<td>
						<?php echo JDAssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, !$isCheckout, false); ?>
					</td>
					<td>
						<?php echo JDAssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, !$isCheckout, true); ?>
					</td>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<td class="small">
							<?php echo $this->escape($item->menutype_title); ?>
						</td>
					<?php endif; ?>
					<?php if (!empty($this->typeFields['access'])) : ?>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
					<?php endif; ?>
					<td class="hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
