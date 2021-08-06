<?php
/**
 * @package jDownloads
 * @version 3.8  
 * @copyright (C) 2007 - 2018 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::_('bootstrap.tooltip');

    HTMLHelper::_('formbehavior.chosen', 'select');
    HTMLHelper::_('behavior.multiselect');
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');    
    
    $user		= JFactory::getUser();

    $listOrder	= $this->escape($this->state->get('list.ordering'));
    $listDirn	= $this->escape($this->state->get('list.direction'));

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		Joomla.submitform(task);
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=groups');?>" method="post" name="adminForm" id="adminForm">

    <?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>	
    
    <?php
    // Search tools bar
    echo JLayoutHelper::render('searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?> 
       
    
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
    

    <div class="alert alert-info" style="margin-top:10px;"><?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_GROUP_TITLE_INFO'); ?> </div>    
	<div class="clr"> </div>

	    <table class="table table-striped" id="groupsList">
            <thead>
                <tr>
                    <th class="center" style="width:2.5%; text-align:right;">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th style="min-width:100px">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_GROUP_TITLE'); ?>
                    </th>
                    <th class="nowrap center" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_IMPORTANCE'); ?>
                    </th> 
                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_USERS_IN_GROUP'); ?>
                    </th>
                    
                    
                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_CAPTCHA'); ?>
                    </th>                    
                    
                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_FORM'); ?>                    
                    </th> 
                     
                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_MUST_FORM_FILL_OUT'); ?>                    
                    </th>

                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_REPORT_FORM'); ?>
                    </th>
                    
                    <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_COUNTDOWN'); ?>
                    </th>
                        <th class="nowrap hidden-phone" style="width:5%;text-align:center;">
                        <?php echo JText::_('COM_JDOWNLOADS_USERGROUP_TAB_LIMITS'); ?>
                    </th>

                    <th class="nowrap" style="width:2.5%;">
                        <?php echo JText::_('COM_JDOWNLOADS_ID'); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="10">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
    
		    <tbody>
		    <?php
            foreach ($this->items as $i => $item) :
                $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			    $canEdit	= $user->authorise('core.admin', 'com_jdownloads');

			    // If this group is super admin and this user is not super admin, $canEdit is false   !!!
			    if (!$user->authorise('core.admin') && (JAccess::checkGroup($item->id, 'core.admin'))) {
				    $canEdit = false;
			    }
		    ?>
			    <tr class="row<?php echo $i % 2; ?>">
				    <td class="center">
					    <?php if ($canEdit) : ?>
						    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
					    <?php endif; ?>
				    </td>
                    
				    <td>
                        <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'groups.', $canCheckin); ?>
                        <?php endif; ?>                
                    
					    <?php 
                        if ($item->level > 0){
                            echo '<span class="muted">' . str_repeat('&#9482;&nbsp;&nbsp;&nbsp;', ((int) $item->level -1)) . '</span>&ndash;&nbsp;';
                        } ?>
                                  
					    <?php if ($canEdit) : ?>
					                <a href="<?php echo JRoute::_('index.php?option=com_jdownloads&task=group.edit&id='.$item->jd_user_group_id);?>">
						            <?php echo $this->escape($item->title); ?></a>
					    <?php else : ?>
						            <?php echo $this->escape($item->title); ?>
					    <?php endif; ?>

				    </td>
                    <td class="center">
                        <?php echo '<span class="badge badge-info">'.$item->importance.'</span>'; ?>
                    </td>
                    
				    <td class="center hidden-phone">
					          <a class="badge <?php if ($item->user_count > 0) echo 'badge-success'; ?>" href="<?php echo JRoute::_('index.php?option=com_users&view=users&filter[group_id]=' . (int) $item->jd_user_group_id); ?>">
                              <?php echo $item->user_count; ?></a>      
				    </td>

                    <td class="center hidden-phone btns">
                        <?php if ($item->view_captcha){
                                  echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                              } else {
                                  echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                              } 
                        ?>
                    </td>                 
                    
                    <td class="center hidden-phone btns">
                        <?php if ($item->view_inquiry_form){
                                  echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                              } else {
                                  echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                              } 
                        ?>
                    </td>                 

                    <td class="center hidden-phone btns">
                        <?php if ($item->must_form_fill_out){
                                  echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                              } else {
                                  echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                              } 
                        ?>
                    </td>

                    <td class="center hidden-phone btns">
                        <?php 
                        if ($item->view_report_form){
                                  echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                              } else {
                                  echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                              } 
                        ?>
                    </td>                 

                    <td class="center hidden-phone btns">
                        <?php if ($item->countdown_timer_duration){
                                  echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                              } else {
                                  echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                              } 
                        ?>
                    </td>                 

                    <td class="center hidden-phone btns">
                        <?php 
                            // Check wheter exists limitations and then add the limitation informations
                            $item->limit_info = '';
                            if ($item->download_limit_daily > 0)          $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_DAILY').': '.$item->download_limit_daily.' - ';
                            if ($item->download_limit_weekly > 0)         $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_WEEKLY').': '.$item->download_limit_weekly.' - ';
                            if ($item->download_limit_monthly > 0)        $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_MONTHLY').': '.$item->download_limit_monthly.' - ';
                            if ($item->download_volume_limit_daily > 0)   $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_DAILY').': '.$item->download_volume_limit_daily.'MB - ';
                            if ($item->download_volume_limit_weekly > 0)  $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_WEEKLY').': '.$item->download_volume_limit_weekly.'MB - ';
                            if ($item->download_volume_limit_monthly > 0) $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_MONTHLY').': '.$item->download_volume_limit_monthly.'MB - ';
                            if ($item->how_many_times > 0)                $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_HOW_MANY_TIMES').': '.$item->how_many_times.' - ';
                            if ($item->upload_limit_daily > 0)            $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_UPLOAD_LIMIT_DAILY').': '.$item->upload_limit_daily.' - ';
                            if ($item->transfer_speed_limit_kb > 0)       $item->limit_info .= JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_TRANSFER_SPEED_LIMIT').': '.$item->transfer_speed_limit_kb.'KB';
                        
                            if ($item->limit_info){
                                // Remove ' - ' at the end when exist
                                $pos = strripos($item->limit_info, ' - ');
                                if ($pos > (strlen($item->limit_info) - 4)){
                                    $item->limit_info = substr($item->limit_info, 0, $pos);
                                }
                                echo '<span class="badge badge-important" rel="tooltip" title="'.$item->limit_info.'">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                            } else {
                                echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                            } 
                        ?>
                    </td> 

				    <td class="center">
					    <?php echo (int) $item->id; ?>
				    </td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
	    </table>
    
    <?php endif;?>

	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
