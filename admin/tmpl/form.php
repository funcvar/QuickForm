<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;
?>

<style>
    #sidebar-wrapper {
        display: none;
    }
</style>
<script>var qffilesmod = <?php echo qf::conf()->get('filesmod')?>,qfcde=<?php echo qf::conf()->get('cod')?1:0?>,qfshopmod=<?php echo qf::conf()->get('shopmod')?>;</script>

<div class="qf_form_style_form">
<form method="post" name="qfadminform" class="formstyle" autocomplete="off">

<?php echo $this->form->renderField('title'); ?>

<div class="formdiv">
    <table id="formtbl">
      <thead>
        <tr><th class="l_td">label:<br /></th><th class="r_td">teg:<br /></th><th class="atr_td"></th><th class="del_td"></th><th class="drag_td"></th></tr>
      </thead>
      <tbody>
                <?php
                if(isset($this->items->fields)){
                    $rows = json_decode($this->items->fields);
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
                            if($row->teg!='recaptcha' && isset($row->related)){
                                $db = \JFactory::getDBO();
                                $db->setQuery('SELECT title FROM #__qf3_forms WHERE id = ' . ( int ) $row->related);
                                $inplab .= $db->loadResult();
                            }
                        }
                        else{
                            $inplab = '<div class="qfsmoll"></div><input name="qfllabel" type="text" value="'.htmlspecialchars($label).'" class="qflabelclass" />';
                        }

                        $html .= '<tr data-settings="'.htmlentities(json_encode($row), ENT_QUOTES, 'UTF-8').'"><td class="l_td">'.$inplab;
                        if($options){
                            $html .= '<div class="optionsBox hid">';
                            foreach($options as $option){
                                $label = htmlspecialchars($option->label);
                                unset($option->label);
                                $html .= '<div class="optionRow" data-settings="'.htmlentities(json_encode($option), ENT_QUOTES, 'UTF-8').'"><input name="qfoption" type="text" value="'.$label.'" /><a href="#" class="setting"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a><a href="#" class="plus"><i class="fa fa-plus" aria-hidden="true"></i></a><a href="#" class="delete"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
                            }
                            $html .= '</div>';
                        }

                        if($row->teg=='select' || $row->teg=='input[radio]' || $row->teg=='qftabs' || $row->teg=='boxadder')
                            $teg = '<span class="smbtogl"></span> <a href="#" class="optionstogler">'.$row->teg.'</a>';
                        else $teg = $row->teg;

                        $html .= '</td><td class="r_td">'.$teg.'</td><td class="atr_td"><a href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></td><td class="drag_td"><a href="#"><i class="fa fa-arrows" aria-hidden="true"></i></a></td><td class="del_td"><a href="#"><i class="fa fa-times" aria-hidden="true"></i></a></td></tr>';
                    }
                    echo $html;
                }
        ?>
      </tbody>
    </table>
</div>
<input type="hidden" name="fields" value="" />
<input type="hidden" name="id" value="<?php echo $this->items->id ?>" />
<input type="hidden" name="def" value="<?php echo $this->items->def ?>" />
<input type="hidden" name="projectid" value="<?php echo $this->items->projectid ?>" />

</form>
</div>
