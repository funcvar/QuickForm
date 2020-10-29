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
$params = JComponentHelper::getParams('com_qf3');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', 'com_qf3');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qf3&task=projects.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'projectsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
	<script type="text/javascript">
		jQuery(document).ready(function ($){
			$('.qfplagin').on('mouseover', function() {
				 var rng, sel;
				 if ( document.createRange ) {
					 rng = document.createRange();
					 rng.selectNode( this )
					 sel = window.getSelection();
					 sel.removeAllRanges();
					 sel.addRange( rng );
				 } else {
					 var rng = document.body.createTextRange();
					 rng.moveToElementText( this );
					 rng.select();
				 }
			});
			$('.qfplagin').on('mouseout', function() {
				this.innerHTML=this.innerHTML+'';
			});
		});
  </script>

<form action="<?php echo JRoute::_('index.php?option=com_qf3&view=projects'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" />
			</div>
      <div class="btn-group pull-left">
        <button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
        <button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
      </div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<?php if(!trim($params->get('cod'))){
				// if ($this->items[0]->hits || sizeof($this->items)>1) {
				// 	echo '<div style="color:red">'.JText::_('QF_ACTIVATE_QF').'</div>';
				// }
			} ?>
			<table class="table table-striped" id="projectsList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
          <th width="20" class="center">
            <?php echo JHtml::_('grid.checkall'); ?>
          </th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="1%">
						<?php echo JHtml::_('grid.sort', 'QF_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
          <th width="10%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
          </th>

					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JText::_('QF_PLAGIN_COD');?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="11">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering   = ($listOrder == 'a.ordering');
				$canCreate  = $user->authorise('core.create',     'com_qf3');
				$canEdit    = $user->authorise('core.edit',       'com_qf3');
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
				$canChange  = $user->authorise('core.edit.state', 'com_qf3') && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">
          <td class="order nowrap center hidden-phone">
            <?php
            $iconClass = '';
            if (!$canChange)
            {
              $iconClass = ' inactive';
            }
            elseif (!$saveOrder)
            {
              $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
            }
            ?>
            <span class="sortable-handler <?php echo $iconClass ?>">
              <span class="icon-menu"></span>
            </span>
            <?php if ($canChange && $saveOrder) : ?>
              <input type="text" style="display:none" name="order[]" size="5"
                value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
            <?php endif; ?>
          </td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'projects.', $canChange, 'cb'); ?>
					</td>
					<td class="nowrap">
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'projects.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_qf3&task=project.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a> > (<a href="<?php echo JRoute::_('index.php?option=com_qf3&view=forms&projectid='.(int) $item->id); ?>">
								<?php echo JText::_('QF_FIELDS'); ?></a>)
						<?php else : ?>
								<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->hits); ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
          <td class="small nowrap hidden-phone">
            <?php if ($item->language == '*'): ?>
              <?php echo JText::alt('JALL', 'language'); ?>
            <?php else: ?>
              <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
            <?php endif; ?>
          </td>

					<td class="center hidden-phone qfplagin" nowrap="nowrap">
						{QF3=<?php echo (int) $item->id; ?>}
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
