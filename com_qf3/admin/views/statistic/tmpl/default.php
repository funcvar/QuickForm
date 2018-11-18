<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
* @license    GNU/GPL
*/
defined('_JEXEC') or die;


JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			if (task == 'statistic.cancel' || document.formvalidator.isValid(document.getElementById('statistic-form')))
			{
				Joomla.submitform(task, document.getElementById('statistic-form'));
			}
		};
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_qf3&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="statistic-form" class="form-validate form-horizontal">
  <div style="padding:20px 20px 20px 80px">
    <h3><?php echo $this->item->st_title; ?></h3>
    <div style="float:left; width:60%;">
      <?php echo $this->item->st_form;  ?>
    </div>
    <div style="float:left; width:40%">
    <?php echo $this->form->getControlGroup('st_status');?>
    <?php echo $this->form->getControlGroup('st_desk');?>
    </div>
  </div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
