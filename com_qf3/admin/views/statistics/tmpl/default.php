<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.id';

?>

<form action="<?php echo JRoute::_('index.php?option=com_qf3&view=statistics'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped" id="statisticList">
			<thead>
				<tr>
      <th width="1%" class="nowrap center hidden-phone">
      </th>
      <th width="1%" class="nowrap center">
        <?php echo JHtml::_('grid.checkall'); ?>
      </th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'JGLOBAL_TITLE', 'a.st_title', $listDirn, $listOrder ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'Status', 'a.st_status', $listDirn, $listOrder ); ?>
			</th>
			<th width="1%"  nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'User', 'a.st_user', $listDirn, $listOrder ); ?>
			</th>
			<th width="140px" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'Date', 'a.st_date', $listDirn, $listOrder ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'IP', 'a.st_ip', $listDirn, $listOrder ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'ID', 'a.id', $listDirn, $listOrder ); ?>
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
			<?php foreach ($this->items as $i => $item) :
				$canEdit    = $user->authorise('core.edit',       'com_qf3');
				?>
				<tr class="row<?php echo $i % 2; ?>" >
					<td class="order nowrap center hidden-phone">
					<?php echo 1+$i; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
			<td>
						<?php if ($canEdit) : ?>
				<a href="index.php?option=com_qf3&view=statistic&id=<?php echo $item->id ?>"><?php echo $this->escape($item->st_title);?></a>
						<?php else : ?>
								<?php echo $this->escape($item->st_title); ?>
						<?php endif; ?>
			</td>
			<td align="center" nowrap="nowrap">
				<?php
					$st_status=Qf3Helper::getStatus();
					echo $st_status[$item->st_status];
        ?>
			</td>
			<td>
				<a href="index.php?option=com_users&task=user.edit&id=<?php echo $item->st_user ?>"><?php echo JFactory::getUser($item->st_user)->get('username');?></a>
			</td>
			<td nowrap="nowrap">
				<?php
				$dat=explode(' ',$item->st_date);
				 echo $dat[0].' <span style="font-size:10px">'.$dat[1].'</span>'; ?>
			</td>
			<td align="center" nowrap="nowrap">
				<?php echo $item->st_ip; ?>
			</td>
			<td align="center">
				<?php echo $item->id; ?>
			</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
