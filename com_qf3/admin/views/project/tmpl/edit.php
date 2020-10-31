<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'administrator/components/com_qf3/assets/project.js', array('version' => 'auto'));
JHtml::_('stylesheet', 'administrator/components/com_qf3/assets/project.css', array('version' => 'auto'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_qf3&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="qfadminForm" id="project-form" class="form-validate form-horizontal">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'qfTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'general', JText::_('QF_GLOBAL_SETTINGS', true)); ?>
				<?php echo $this->form->getControlGroups('general'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'formparams', JText::_('QF_FORM_SETTINGS', true)); ?>
			<?php echo $this->form->getControlGroups('formparamsset'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'emailparams', JText::_('QF_GLOBAL_EMAIL', true)); ?>
			<?php echo $this->form->getControlGroups('emailparamsset'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'calculatorparams', JText::_('QF_CALCULATOR', true)); ?>
			<?php echo $this->form->getControlGroups('calculatorparamsset'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'params', JText::_('JGLOBAL_FIELDSET_ADVANCED', true)); ?>
			<?php echo $this->form->getControlGroups('params'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
