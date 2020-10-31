<?php
/**
* @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('script', 'administrator/components/com_qf3/assets/project.js', array('version' => 'auto'));
JHtml::_('stylesheet', 'administrator/components/com_qf3/assets/project.css', array('version' => 'auto'));
$qf_config = JComponentHelper::getParams('com_qf3');

?>

<form action="<?php echo JRoute::_('index.php?option=com_qf3'); ?>" method="post" name="qfadminForm" id="cart-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'qfTab', array('active' => 'minicart')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'minicart', JText::_('QF_MINICART_SET', true)); ?>
				<?php echo $this->form->getControlGroups('minicart'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'bigcart', JText::_('QF_CART_WINDOW', true)); ?>
			<?php echo $this->form->getControlGroups('bigcart'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'emailset', JText::_('QF_GLOBAL_EMAIL', true)); ?>
			<?php echo $this->form->getControlGroups('emailset'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'discountset', JText::_('QF_FUNCTIONALITY', true)); ?>

		<?php  echo '<div class="control-group title2"><label>'.JText::_('QF_ADD_FILES').'</label></div>'?>
		<?php if(!$qf_config->get('filesmod')) echo '<div class="control-group title3"><label>'.JText::_('QF_FILES_ACTIVAT').'</label></div>'?>

			<?php echo $this->form->getControlGroups('discountset'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'qfTab', 'params', JText::_('JGLOBAL_FIELDSET_ADVANCED', true)); ?>
			<?php echo $this->form->getControlGroups('params'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
