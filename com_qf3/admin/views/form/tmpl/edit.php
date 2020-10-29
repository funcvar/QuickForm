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
$this->document->addScript( '//code.jquery.com/ui/1.11.4/jquery-ui.min.js', 'text/javascript');

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			if (task == 'addfild')
			{
				return false;
			}
			else if (task == 'form.cancel' || document.formvalidator.isValid(document.getElementById('form-form')))
			{
				QuickForm.submitform(task, document.getElementById('form-form'));
			}
		};
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_qf3&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="form-form" class="form-validate form-horizontal" autocomplete="off">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<?php
					echo $this->form->getControlGroup('title');
				?>
			</div>
		</div>
	</div>
  <div class="formdiv">
    <table id="formtbl">
      <thead>
        <tr><th class="l_td">label:<br /></th><th class="r_td">teg:<br /></th><th class="atr_td"></th><th class="del_td"></th><th class="drag_td"></th></tr>
      </thead>
      <tbody>
				<?php
				if($this->item->fields){
					$rows = json_decode($this->item->fields);
					$html = '';
					foreach($rows as $row){
						$label = isset($row->label)?$row->label:'';
						unset($row->label);
						$options = isset($row->options)?$row->options:'';
						unset($row->options);

						if($row->teg=='customHtml'){
							$inplab = '<textarea class="qflabelclass">'.$label.'</textarea>';
						}
						elseif($row->teg=='recaptcha' || $row->teg=='cloner' || $row->teg=='qfincluder'){
							$inplab = '<input type="hidden" value="" class="qflabelclass" />';
							if($row->teg=='qfincluder' && isset($row->related)){
								$db = JFactory::getDBO();
								$db->setQuery('SELECT title FROM #__qf3_forms WHERE id = ' . ( int ) $row->related);
								$inplab .= $db->loadResult();
							}
						}
						else{
							$title = (isset($row->placeholder) && $row->placeholder)?'<div class="qfsmoll">'.$row->placeholder.'</div>':'';
							$calc = (isset($row->math) && $row->math !=='')?' calc':'';
							$related = (isset($row->related) && $row->related)?' related':'';
							$inplab = $title.'<input name="qfllabel" type="text" value="'.$label.'" class="qflabelclass'.$calc.$related.'" />';
						}

						$html .= '<tr data-settings="'.htmlentities(json_encode($row), ENT_QUOTES, 'UTF-8').'"><td class="l_td">'.$inplab;
						if($options){
							$html .= '<div class="optionsBox hid">';
							foreach($options as $option){
								$label = $option->label;
								unset($option->label);
								$calc = (isset($option->math) && $option->math !=='')?' calc':'';
								$related = (isset($option->related) && $option->related)?' related':'';
								$html .= '<div class="optionRow" data-settings="'.htmlentities(json_encode($option), ENT_QUOTES, 'UTF-8').'"><input name="qfoption" class="'.$calc.$related.'" type="text" value="'.$label.'" /><a href="#" class="setting">&#128736;</a><a href="#" class="plus">+</a><a href="#" class="delete">&#10006;</a></div>';
							}
							$html .= '</div>';
						}

						if($row->teg=='select' || $row->teg=='input[radio]' || $row->teg=='qftabs')
							$teg = '<span class="smbtogl"></span> <a href="#" class="optionstogler">'.$row->teg.'</a>';
						else $teg = $row->teg;

						$req = (isset($row->required) && $row->required)?' req':'';

						$html .= '</td><td class="r_td'.$req.'">'.$teg.'</td><td class="atr_td"><a href="#">&#128736;</a></td><td class="drag_td"><a href="#">&#8661;</a></td><td class="del_td"><a href="#">&#10006;</a></td></tr>';
					}
					echo $html;
				}
        ?>
      </tbody>
    </table>
 </div>
	<input type="hidden" name="fields" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="projectid" value="<?php echo $this->item->projectid ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
