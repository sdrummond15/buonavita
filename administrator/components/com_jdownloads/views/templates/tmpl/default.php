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

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.multiselect');
    HTMLHelper::_('behavior.formvalidator');    
    HTMLHelper::_('formbehavior.chosen', 'select');

    $user        = JFactory::getUser();
    $userId      = $user->get('id');
    
    $params = $this->state->params;

    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
    $options['base_path'] = $basePath;

    $layout_previews_url = 'https://www.jdownloads.net/help-server/layout-previews/';

    $listOrder = str_replace(' ' . $this->state->get('list.direction'), '', $this->state->get('list.fullordering'));
    $listDirn  = $this->escape($this->state->get('list.direction')); 

    $canOrder  = $user->authorise('core.edit.state', 'com_jdownloads');

?>
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=templates&type='.$this->jd_tmpl_type.'');?>" method="POST" name="adminForm" id="adminForm">

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

        <div class="alert alert-info" style="margin-top:10px;"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_LOCKED_DESC'); ?> </div>
        <div class="clr"> </div>
        
        <table class="table table-striped" id="logsList">
            <thead>
                <tr>
                    <th class="center" style="width:1%;">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    
                    <th class="nowrap" style="min-width:80px;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_TEMPLIST_TITLE', 'a.template_name', $listDirn, $listOrder ); ?>
                    </th>

                    <th class="nowrap center" style="min-width:60px;">
                        <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_PREVIEW');  ?> 
                    </th>
                    
                    <th class="nowrap hidden-phone">
                        <?php echo  JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_TYP'); ?>
                    </th>

                    <?php 
                        //Only for Categories and Downloads layouts
                        if ($this->jd_tmpl_type == 1 || $this->jd_tmpl_type == 2 || $this->jd_tmpl_type == 8) { ?>
                            <th class="center hidden-phone">   
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_COLS');  ?> 
                            </th>
                        <?php }
                    ?>
                        
                    <?php    
                        // Only for Downloads layouts
                        if ($this->jd_tmpl_type == 2) { ?>    
                            <th class="center hidden-phone">   
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_CHECKBOX_TITLE');  ?> 
                            </th>                            
                            
                            <th class="center hidden-phone">   
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SYMBOLE_TITLE');  ?> 
                            </th>                            
                        <?php }
                    ?>
                    
                    <th class="center hidden-phone">
                        <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_USES_BOOTSTRAP');  ?> 
                    </th>
                    
                    <th class="center hidden-phone">   
                        <?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_USES_W3CSS');  ?> 
                    </th>
                    
                    <th class="center hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_TEMPLIST_LOCKED', 'a.locked', $listDirn, $listOrder ); ?>
                    </th>
                    
                    <th class="center">                        
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_TEMPLIST_ACTIVE', 'a.template_active', $listDirn, $listOrder ); ?>
                    </th>

                    <th class="nowrap hidden-phone" style="width: 1%;">                        
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ID', 'a.id', $listDirn, $listOrder ); ?>
                    </th>
                </tr>    
            </thead>
            <tfoot>
                <tr>
                    <td colspan="11">
                        <?php echo '<br />'.$this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>    
                <?php
                    foreach ($this->items as $i => $item) {
                        $link         = JRoute::_( 'index.php?option=com_jdownloads&task=template.edit&id='.(int) $item->id.'&type='.(int) $item->template_typ);
                        $canCheckin   = $user->authorise('core.manage',     'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
                        $canChange    = $user->authorise('core.edit.state', 'com_jdownloads') && $canCheckin;
                        $canCreate    = $user->authorise('core.create',     'com_jdownloads');
                        $canEdit      = $user->authorise('core.edit',       'com_jdownloads');
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            
                            <td>
                            <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $user->name, $item->checked_out_time, 'templates.', $canCheckin); ?>
                            <?php endif; ?>

                            <?php if ($canEdit) : ?>
                                <a href="<?php echo $link; ?>">
                                    <?php echo $this->escape($item->template_name); ?></a>
                            <?php else : ?>
                                    <?php echo $this->escape($item->template_name); ?>
                            <?php endif; ?>
                            
                                <p class="smallsub">
                                    <?php echo $this->escape($item->note);?>
                                </p>
                            </td>
                            
                            <td class="center">
                                
                                <?php
                                    $img = 'layout_type_'.(int)$this->jd_tmpl_type.'_'.(int)$item->preview_id.'.gif';
                                    $thumb = 'layout_type_'.(int)$this->jd_tmpl_type.'_'.(int)$item->preview_id.'_thumb.gif';
                                    clearstatcache();
                                    if (JDownloadsHelper::url_check($layout_previews_url.$img)){
                                        if ($params->get('use_lightbox_function')){
                                            echo '<a href="'.$layout_previews_url.$img.'" data-lightbox="lightbox'.$item->id.'" data-title="'.JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_PREVIEW_NOTE').'" target="_blank"><img src="'.$layout_previews_url.$thumb.'" class="img-polaroid" alt="'.$thumb.'" style="width:50px; height:50px"></a>';    
                                        } else {
                                            echo '<a href="'.$layout_previews_url.$img.'" target="_blank"><img src="'.$layout_previews_url.$thumb.'" class="img-polaroid" alt="'.$thumb.'" style="width:50px; height:50px"></a>';    
                                        }
                                    } else {
                                        echo '<img src="'.$layout_previews_url.'no_pic.gif'.'" class="img-polaroid" alt="" style="width:50px; height:50px"></a>';
                                    }
                                ?>
                                
                            </td>
                            
                            <td class="nowrap hidden-phone">
                                <?php echo $this->temp_type_name[$item->template_typ]; ?>
                            </td>

                            <?php if ($this->jd_tmpl_type == 1 || $this->jd_tmpl_type == 2 || $this->jd_tmpl_type == 8) { ?>
                                <td class="center hidden-phone btns">   
                                    <?php echo '<span class="badge">'.(int)$this->escape($item->cols).'</span>'; ?> 
                                </td>                        
                            <?php } ?>

                            <?php if ($this->jd_tmpl_type == 2) { ?>    
                                    <td class="center hidden-phone btns">   
                                    <?php if (!$item->checkbox_off){
                                              echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          } 
                                    ?>
                                    </td>                            

                                    <td class="center hidden-phone btns">
                                    <?php if (!$item->symbol_off){
                                              echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          } 
                                    ?>
                                    </td>                            
                            <?php } ?>
                            
                            <td class="center hidden-phone btns">   
                                    <?php if ($item->uses_bootstrap){
                                              echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          } 
                                    ?>
                            </td>
                                
                            <td class="center hidden-phone btns">   
                                    <?php if ($item->uses_w3css){
                                              echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          } 
                                    ?>
                            </td>                                
                            
                            <td class="center hidden-phone"> 
                                <?php
                                    if ($item->locked) {
                                              echo '<span class="badge badge-warning">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          }
                                    ?>                
                            </td>
                    
                            <td class="center"> 
                                <?php
                                    if ($item->template_active) {
                                              echo '<span class="badge badge-success">'.JText::_('COM_JDOWNLOADS_YES').'</span>';
                                          } else {
                                              echo '<span class="badge">'.JText::_('COM_JDOWNLOADS_NO').'</span>';
                                          }
                                ?>
                            </td>
                            
                            <td class="nowrap hidden-phone">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php 
                    }
                    ?>
            </tbody>
        </table>
    <?php endif;?>
</div>

<div>
    <input type="hidden" name="option" value="com_jdownloads" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="type" value="<?php echo (int)$this->jd_tmpl_type; ?>" />
    <?php echo JHtml::_('form.token'); ?>    
</div>
</form>
