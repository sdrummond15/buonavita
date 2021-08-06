<?php 
/**
 * @package jDownloads
 * @version 2.5  
 * @copyright (C) 2007 - 2013 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access'); 

    JHtml::_('bootstrap.tooltip');
    jimport( 'joomla.form.form' ) 
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif;?>

    <div>
            <div class="alert alert-info"><?php echo JText::_('COM_JDOWNLOADS_OPTIONS_EXPORT_INFO_DESC'); ?></div>
    </div>
    
    <input type="hidden" name="controller" value="optionsexport" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_jdownloads" />

    <?php echo JHtml::_('form.token'); ?>
</form>
