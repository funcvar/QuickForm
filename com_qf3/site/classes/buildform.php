<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();
class QuickForm3
{
    public function __construct()
    {
        $this->lang = JFactory::getLanguage();
        $this->lang->load('com_qf3');
        $this->db = JFactory::getDBO();
        $this->user = JFactory::getUser();
        $xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_qf3/qf3.xml');
        $this->version = preg_replace("/[^0-9]/", '', (string)$xml->version);
    }

    public function getQuickForm($id)
    {
        $html = '';
        $app = JFactory::getApplication();
        $groups = $this->user->getAuthorisedViewLevels();

        $ajaxquery = $app->input->get('task')=='ajax';
        $project = $this->getProjectById($id);

        if (! empty($project)) {
            if($project->formparams->cssform != -1) {
                $formclass = ' '.str_replace(array('.css','-1'), '', $project->formparams->cssform);
            }else{
                $formclass = '';
            }

            if ($project->published && in_array($project->access, $groups)) {
                if ($project->language == $this->lang->getTag() || $project->language == '*') {
                    if (!$ajaxquery) {
                        JHtml::_('jquery.framework');

                        if ($project->formparams->cssform != -1) {
                            JHtml::_('stylesheet', 'components/com_qf3/assets/css/' . $project->formparams->cssform, array('version' => $this->version));
                        }

                        JHtml::_('script', 'components/com_qf3/assets/js/' . $project->formparams->jsform, array('version' => $this->version));

                        if ($this->get('loadcalendar', $project->formparams)) {
                            JHtml::_('stylesheet', 'components/com_qf3/assets/datepicker/css/datepicker.css', array('version' => $this->version));
                            JHtml::_('script', 'components/com_qf3/assets/datepicker/js/datepicker.js', array('version' => $this->version));
                        }

                        if ($project->formparams->modal) {
                            $html .= '<a href="javascript:void(0);" class="qf3modal" data-project="'.$project->id.'" data-url="'.JURI::current().'" data-class="'.trim($formclass).'">'.$this->mlangLabel($project->formparams->modallink).'</a>';

                            return $html;
                        }
                    }

                    $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE def=1 AND projectid = ' . ( int ) $project->id);
                    $form = $this->db->loadObject();

                    if (! empty($form)) {
                        $html .= $this->getFields($form);
                    }
                }
            }
        } else {
            return 'QF3 project with id ' . $id . ' not found.';
        }

        if ($html) {
            $rethtml = '';

            $rethtml .=  '<div class="qf3form'.$formclass.'"><form method="post" enctype="multipart/form-data" autocomplete="'.$project->formparams->autocomplete.'">' . $html . '<input name="option" type="hidden" value="com_qf3" /><input name="id" type="hidden" value="' . $id . '" />' . JHtml::_('form.token');

            $type = $project->calculatorparams->calculatortype;

            if ($type) {
                $rethtml .= '<input name="calculatortype" type="hidden" value="' . $type . '" />';
                if ($type != 'default' && $type != 'custom') {
                    $formula=preg_replace('/\s*\t*/', '', $project->calculatorparams->calcformula);
                    $rethtml .= '<input name="calcformula" type="hidden" data-formula="' . $formula . '" />';
                }
            }

            if ($project->formparams->ajaxform) {
                $rethtml .= '<input name="task" type="hidden" value="ajax" /><input name="mod" type="hidden" value="qfajax" />';
            } else {
                $rethtml .= '<input name="task" type="hidden" value="" />';
            }

            if ($project->formparams->qfkeepalive) {
                $config= new JConfig();
                $qfkeepalive = 60000*(($config->lifetime)-0.5);
                $rethtml .= '<input name="qfkeepalive" type="hidden" value="'.$qfkeepalive.'" class="qfkeepalive" />';
            }

            $rethtml .= '<input name="root" type="hidden" value="' . JURI::current() . '" />' . $this->getQFlink() . '</form></div>';

            return $rethtml;
        }
    }


    protected function getDataById($id)
    {
        $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE id = ' . ( int ) $id);
        return $this->db->loadObject();
    }

    protected function getProjectById($id)
    {
        $this->db->setQuery('SELECT * FROM #__qf3_projects WHERE id = ' . ( int ) $id);
        $project = $this->db->loadObject();

        if ($project) {
            $project->params = json_decode($project->params);
            $project->formparams = json_decode($project->formparams);
            $project->emailparams = json_decode($project->emailparams);
            $project->calculatorparams = json_decode($project->calculatorparams);

            if ($project->params->languagelink) {
                $this->lang->load($project->params->languagelink);
            }
        }

        $this->project = $project; // qfcart

        return $project;
    }

    protected function getFields($form)
    {
        $html = '';
        $fields = json_decode($form->fields);
        $id = $form->id;

        foreach ($fields as $field) {
            switch ($field->teg) {
              case 'select':
                $html .= $this->qSelect($field, $id);
              break;
              case 'input[radio]':
                $html .= $this->qRadio($field, $id);
              break;
              case 'input[checkbox]':
                $html .= $this->qCheckbox($field, $id, 'qfcheckbox');
              break;
              case 'qf_checkbox':
                $html .= $this->qCheckbox($field, $id, 'qf_checkbox');
              break;
              case 'userName':
                $html .= $this->qUserName($field, $id);
              break;
              case 'userPhone':
                $html .= $this->qUserPhone($field, $id);
              break;
              case 'userEmail':
                $html .= $this->qUserEmail($field, $id);
              break;
              case 'input[file]':
                $html .= $this->qFile($field, $id);
              break;
              case 'textarea':
                $html .= $this->qTextarea($field, $id);
              break;
              case 'submit':
                $html .= $this->qSubmit($field, $id);
              break;
              case 'customHtml':
                $html .= $this->qCustomHtml($field, $id);
              break;
              case 'customPhp':
                $html .= $this->qCustomPhp($field, $id);
              break;
              case 'calculatorSum':
                $html .= $this->qCalculatorSum($field, $id);
              break;
              case 'recaptcha':
                $html .= $this->qRecaptcha($field, $id);
              break;
              case 'backemail':
                $html .= $this->qBackemail($field, $id);
              break;
              case 'cloner':
                $html .= $this->qCloner($field, $id);
              break;
              case 'qfincluder':
                $html .= $this->qQfincluder($field, $id);
              break;
              case 'addToCart':
                $html .= $this->qAddToCart($field, $id);
              break;
              case 'qftabs':
                $html .= $this->qTabs($field, $id);
              break;
              case 'qf_number':
                $html .= $this->qQfnumber($field, $id);
              break;
              case 'qf_range':
                $html .= $this->qQfslider($field, $id);
              break;
              case 'qfcalendar':
                $html .= $this->qQfcalendar($field, $id);
              break;
              case 'stepperbox':
                $html .= $this->qStepper($field, $id);
              break;
              case 'stepperbtns':
                $html .= $this->qStepperbtns($field, $id);
              break;
              default:
                $html .= $this->qInput($field, $id);
          }
        }

        return $html;
    }

    protected function mlangLabel($val)
    {
        if (strpos($val, 'QF_')===0) {
            return JText::_($val);
        }
        return $val;
    }

    protected function boxClass($field)
    {
        if ($this->get('class', $field)) {
            $arr = explode(' ', $field->class);
            foreach ($arr as $k=>$v) {
                $arr[$k] = ' box_'.$v;
            }
            return implode('', $arr);
        }
        return '';
    }

    protected function get($val, $field)
    {
        if (isset($field->$val)) {
            return $field->$val;
        }
        return '';
    }

    protected function getLabel($field, $for = '', $class = 'qf3label')
    {
        if (!isset($field->label)) {
            return '';
        }

        $html = '';
        if ($for) {
            $for = ' for="' . $for . '"';
        }

        if ($field->label) {
            $html .= '<label class="'.$class.'"'.$for.'>' . $this->mlangLabel($field->label) . ($this->get('required', $field) ? ' <span class="qf3labelreq">*</span>' : '') . '</label>';
        } else {
            $html .= '<label class="'.$class.'"'.$for.'></label>';
        }
        return $html;
    }

    protected function attr($arr, $field)
    {
        $html = '';
        foreach ($arr as $v) {
            if ($v=='class') {
                $html .= $this->get('class', $field) ? ' class="' . $field->class . '"' : '';
            }
            if ($v=='required') {
                $html .= $this->get('required', $field) ? ' required' : '';
            }
            if ($v=='custom') {
                $custom = $this->get('custom', $field) ? ' ' . $field->custom : '';
                if (strpos($custom, 'value="QF_')!==false) {
                    $custom = preg_replace_callback('/value="(.*?)"/', function ($m) {
                        return str_replace($m[1], JText::_($m[1]), $m[0]);
                    }, $custom);
                }
                $html .= $custom;
            }
            if ($v=='placeholder') {
                $html .= $this->get('placeholder', $field) !== '' ? ' placeholder="' . $this->mlangLabel($field->placeholder) . '"' : '';
            }
            if ($v=='checked') {
                $html .= $this->get('checked', $field) ? ' checked' : '';
            }
            if ($v=='value') {
                $html .= $this->get('value', $field) ? ' value="' . $this->mlangLabel($field->value) . '"' : '';
            }
        }
        return $html;
    }


    protected function qCustomPhp($field, $id)
    {
        $cod = $this->get('customphp1', $field);
        if (!$cod) {
            return '';
        }

        $res = '';
        $config = JFactory::getConfig();
        $tmpfname = tempnam($config->get('tmp_path'), "qf");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $cod, strlen($cod));
        fclose($handle);
        if (is_file($tmpfname)) {
            ob_start();
            include $tmpfname;
            $res =  ob_get_clean();
        }
        unlink($tmpfname);
        $html = '<div class="qf3 qfphp">';
        $html .= $this->getLabel($field);
        $html .= $res;
        $html .= '</div>';

        return $html;
    }

    protected function qSelect($field, $id)
    {
        $options = $this->get('options', $field);
        $settings = '';

        $html = '';
        $html .= '<div class="qf3 qfselect' . ($this->get('required', $field) ? ' req' : '') .$this->boxClass($field). '">';
        $html .= $this->getLabel($field);

        foreach ($options as $option) {
            if ($this->get('math', $option)) {
                $settings = ' data-settings="' . htmlentities('{"fildid":"' . $id . '.' . $field->fildnum . '"}') . '"';
                break;
            }
        }

        $html .= '<select name="qfselect[]"' . $this->attr(array('class', 'custom', 'required'), $field) . $settings.'>';

        $i = '';
        foreach ($options as $option) {
            $arr = array();
            $related = $this->get('related', $option);
            $math = $this->get('math', $option);

            $html .= '<option value="' . $i . '"';

            if ($related || $math !== '') {
                if ($related) {
                    $arr[] = '"related":"' . $related . '"';
                }
                if ($math !== '') {
                    $arr[] = '"math":"' . $math . '"';
                }
                $html .= ' data-settings="' . htmlentities('{'.implode(',', $arr).'}') . '"';
            }

            $html .= '>' . $this->mlangLabel($option->label) . '</option>';
            $i ++;
        }

        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    protected function qStepper($field, $id) {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';
        $related = $this->get('related', $field) ? (int) $field->related : 0;

        if (! $related) {
            return 'qStepper: Field group id not specified.';
        }
        if ($related == $id) {
            return 'recursion error';
        }

        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }

        $html = '';

        if ($field->label) {
            $html .= '<div class="qfstepperlabel">' . $this->mlangLabel($field->label) . '</div>';
        }
        $html .= '<div class="qfstepper' . $class . '">';
        $html .= '<div class="qfstepperinner">';
        $html .= $this->getFields($data);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function qStepperbtns($field, $id) {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';
        $related = $this->get('related', $field) ? (int) $field->related : 0;
        $prev = $this->get('prev', $field);
        $next = $this->get('next', $field);

        $html = '';
        $html .= '<div class="qfstepperbtns' . $class . '">';
        $html .= '<div class="qfprev">'.$prev.'</div><div class="qfnext" data-next="'.$related.'">'.$next.'<input name="qfstepper[]" type="hidden" value="0" /></div>';
        $html .= '</div>';

        return $html;
    }


    protected function qTabs($field, $id)
    {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';
        $options = $this->get('options', $field);
        $orient = $this->get('orient', $field) ? ' horizontally' : ' vertically';
        $field->fildid = $id . '.' . $field->fildnum;

        $html = '';

        if ($field->label) {
            $html .= '<div class="qftabslabel">' . $this->mlangLabel($field->label) . '</div>';
        }
        $html .= '<div class="qftabs' . $orient . $class . '">';

        $html .= '<div class="qftabslabelsbox">';
        $i = 0;
        foreach ($options as $option) {
            $additionalclass = ($i%2)?' qfodd':' qfeven';
            $activ = ($i==0)?' qftabactiv':'';

            $html .= '<div class="qftabsitemlabel'.$additionalclass.$activ.'">' . $this->mlangLabel($option->label) . '</div>';
            $i ++;
        }
        $html .= '</div>';


        $i = 0;
        foreach ($options as $option) {
            $related = $this->get('related', $option);
            $display = ($i==0)?'':' style="display:none"';

            $html .= '<div class="qftabsitem"'.$display.'>';

            if ($related) {
                $data = $this->getDataById($related);
                if (!empty($data)) {
                    $html .= $this->getFields($data);
                }
            }

            $html .= '</div>';
            $i ++;
        }

        $html .= '</div>';

        return $html;
    }

    protected function qQfnumber($field, $id)
    {
        $class = $this->get('class', $field) ? ' class="' . $field->class . '"' : '';
        $required = $this->get('required', $field) ? ' required' : '';
        $custom = $this->get('custom', $field) ? ' ' . $field->custom : '';
        $placeholder = $this->get('placeholder', $field) !== '' ? ' placeholder="' . $this->mlangLabel($field->placeholder) . '"' : '';
        $orient = $this->get('orient', $field) ? ' horizontally' : ' vertically';
        $math = $this->get('math', $field);
        $field->fildid = $id . '.' . $field->fildnum;
        $value = 0;

        if ($custom) {
            if (preg_match("/\svalue=\"(\d*[.,]?\d*)\"/", $custom, $m)) {
                $custom = str_replace(' value="'.$m[1].'"', '', $custom);
                $value = $m[1];
            }
        }

        $html = '';
        $html .= '<div class="qf3 qf_number' . $orient. ($required ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);
        $html .= '<div class="qf_number_inner">';

        $html .= '<input type="number" name="qfnumber[]" value="'.$value.'"' . $class . $custom . $required . $placeholder;
        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fildid":"' . $field->fildid . '"}') . '"';
        }
        $html .= ' />';

        $html .= '<div class="number__controls"><button type="button" class="qfup">+</button><button type="button" class="qfdown">−</button></div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function qQfslider($field, $id)
    {
        $class = $this->get('class', $field) ? ' class="' . $field->class . '"' : '';
        $required = $this->get('required', $field) ? ' required' : '';
        $custom = $this->get('custom', $field) ? ' ' . $field->custom : '';
        $orient = $this->get('orient', $field) ? ' vertically' : ' horizontally';
        $math = $this->get('math', $field);
        $field->fildid = $id . '.' . $field->fildnum;
        $min = 0;
        $max = 100;
        if ($custom) {
            if (preg_match("/\smin=\"(\d*[.,]?\d*)\"/", $custom, $m)) {
                $custom = str_replace(' min="'.$m[1].'"', '', $custom);
                $min = $m[1];
            }
            if (preg_match("/\smax=\"(\d*[.,]?\d*)\"/", $custom, $m)) {
                $custom = str_replace(' max="'.$m[1].'"', '', $custom);
                $max = $m[1];
            }
        }

        $html = '';
        $html .= '<div class="qf3 qfslider' . $orient. ($required ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);
        $html .= '<div class="qfslider_inner">';

        $html .= '<div class="slider_min">'.$min.'</div><div class="slider_chosen"></div><div class="slider_max">'.$max.'</div>';

        $html .= '<input type="range" name="qfrange[]" min="'.$min.'" max="'.$max.'"' . $class . $custom . $required;
        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fildid":"' . $field->fildid . '"}') . '"';
        }
        $html .= ' />';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function qQfcalendar($field, $id)
    {
      $double = $this->get('double', $field);
      $format = $this->get('format', $field) ? $field->format : 'Y-m-d';
      $math = $this->get('math', $field);

      JHtml::_('stylesheet', 'components/com_qf3/assets/datepicker/css/datepicker.css', array('version' => $this->version));
      JHtml::_('script', 'components/com_qf3/assets/datepicker/js/datepicker.js', array('version' => $this->version));

      $html = '';
      $html .= '<div class="qf3 qfcalendar qf3txt' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
      $html .= $this->getLabel($field);

      if($double) {

          if($val1 = $this->get('val1', $field)){
              $rest = (int) substr($val1, 1);
              if($val1{0}=='+')$val1 = date($format, (time()+3600*24*$rest));
              elseif($val1{0}=='-')$val1 = date($format, (time()-3600*24*$rest));
          }
          else $val1 = date($format);

          if($val2 = $this->get('val2', $field)){
              $rest = (int) substr($val2, 1);
              if($val2{0}=='+')$val2 = date($format, (time()+3600*24*$rest));
              elseif($val2{0}=='-')$val2 = date($format, (time()-3600*24*$rest));
          }
          else $val2 = date($format);

        $html .= '<div class="double">';
          $html .= '<div class="double_inner">';
            $html .= '<div class="qf_date">';
              $field->value = $val1;
              $html .= '<div class="qf_date_label">'.$this->get('leb1', $field).'</div>';
              $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required', 'value'), $field) . ' /><a href="#" class="qf_date_a"></a></div>';
              $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
            $html .= '<div class="qf_date">';
              $field->value = $val2;
              $html .= '<div class="qf_date_label">'.$this->get('leb2', $field).'</div>';
              $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required', 'value'), $field) . ' /><a href="#" class="qf_date_a"></a></div>';
              $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
          $html .= '</div>';
        $html .= '</div>';
      }
      else {
          if($field->value = $this->get('value', $field)){
              $rest = (int) substr($field->value, 1);
              if($field->value[0]=='+')$field->value = date($format, (time()+3600*24*$rest));
              elseif($field->value[0]=='-')$field->value = date($format, (time()-3600*24*$rest));
          }
          else $field->value = date($format);

        $html .= '<div class="single">';
          $html .= '<div class="single_inner">';
            $html .= '<div class="qf_date">';
              $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required', 'value'), $field) . ' /><a href="#" class="qf_date_a"></a></div>';
              $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
          $html .= '</div>';
        $html .= '</div>';
      }

      $params[] = '"format":"' . $format . '"';
      $params[] = '"fildid":"' . $id . '.' . $field->fildnum . '"';
      if($math) $params[] = '"math":"' . $math . '"';

      $html .= '<input class="calendar_inp" type="hidden" data-settings="' . htmlentities('{'.implode($params, ',').'}') . '" />';

      $html .= '</div>';

      return $html;

    }


    protected function qRadio($field, $id)
    {
        $html = '';
        $orient = $this->get('orient', $field) ? ' horizontally' : ' vertically';
        $custom = $this->get('custom', $field) ? ' ' . $field->custom : '';
        $class = $this->get('class', $field);
        if ($class) {
            $class2 = explode(' ', $class)[0];
        }
        $field->fildid = $id . '.' . $field->fildnum;

        $html .= '<div class="qf3 qfradio' . $orient . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);
        $html .= '<div class="radioblok">';

        $i = '';
        $name = str_replace('.', '', 'r_' . microtime(1) . $field->fildid);

        foreach ($field->options as $option) {
            $arr = array();
            $related = $this->get('related', $option);
            $math = $this->get('math', $option);

            $html .= '<input type="radio" id="' . $name . (int)$i . '" name="' . $name . '"' . ($class ? ' class="' . $class2.'_'. (int)$i .' '.$class . '"' : '') . $custom . ' value="' . $i . '"' . (! $i ? ' checked' : '');

            if ($related || $math !== '') {
                if ($related) {
                    $arr[] = '"related":"' . $related . '"';
                }
                if ($math !== '') {
                    $arr[] = '"math":"' . $math . '"';
                }
                $arr[] = '"fildid":"' . $field->fildid . '"';
                $html .= ' data-settings="' . htmlentities('{'.implode(',', $arr).'}') . '"';
            }

            $html .= ' /><label for="' . $name . (int)$i . '"'.($class ? ' class="l_' . $class2.'_'. (int)$i . '"' : '').'>' . $this->mlangLabel($option->label) . '</label>';
            $i ++;
        }

        $html .= '<input name="qfradio[]" type="hidden" value="0" />';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function qFile($field, $id)
    {
        $pos = $this->get('pos', $field);
        $rand = str_replace('.', '', 'f_' . microtime(1) . $id.$field->fildnum);

        $html = '';
        $html .= '<div class="qf3 qffile' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        if ($pos) {
            $html .= '<label class="qf3label"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label filelabel');
        }

        $html .= '<input type="file" id="' . $rand . '" name="inpfile[]"' . $this->attr(array('class', 'custom', 'required'), $field) . ' value="" />';
        if ($pos) {
            $html .= $this->getLabel($field, $rand, 'filelabel');
        }
        $html .= '<input name="qffile[]" type="hidden" value="0" />';
        $html .= '</div>';

        return $html;
    }

    protected function qCheckbox($field, $id, $class)
    {
        $related = $this->get('related', $field);
        $math = $this->get('math', $field);
        $pos = $this->get('pos', $field);
        $field->fildid = $id . '.' . $field->fildnum;

        $rand = str_replace('.', '', 'f_' . microtime(1) . $field->fildid);

        $html = '';
        $html .= '<div class="qf3 '.$class . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        if ($pos) {
            $html .= '<label class="qf3label qfempty"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label qfbefore');
        }

        $html .= '<div class="qfchkbx"><input id="' . $rand . '" type="checkbox" name="chbx"' . $this->attr(array('class', 'custom', 'checked', 'required'), $field);

        if ($related || $math !== '') {
            if ($related) {
                $arr[] = '"related":"' . $related . '"';
            }
            if ($math !== '') {
                $arr[] = '"math":"' . $math . '"';
            }
            $arr[] = '"fildid":"' . $field->fildid . '"';
            $html .= ' data-settings="' . htmlentities('{'.implode(',', $arr).'}') . '"';
        }
        $html .= ' />';

        if ($pos) {
            $html .= $this->getLabel($field, $rand, 'chbxlabel');
        } elseif($class == 'qf_checkbox') {
          $field->label = '';
          $html .= $this->getLabel($field, $rand, 'chbxlabel');
        }

        $html .= '<input name="qfcheckbox[]" type="hidden" value="0" />';
        $html .= '</div></div>';

        return $html;
    }

    protected function qUserName($field, $id)
    {
        $name = $this->user->get('name');
        $value = $name ? ' value="' . $name . '"' : '';

        $html = '';
        $html .= '<div class="qf3 qf3txt qftext' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input type="text" name="qfusername[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required'), $field) . $value . ' />';

        $html .= '</div>';

        return $html;
    }

    protected function qUserEmail($field, $id)
    {
        $email = $this->user->get('email');
        $value = $email ? ' value="' . $email . '"' : '';

        $html = '';
        $html .= '<div class="qf3 qf3txt qftext' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input type="email" name="qfuseremail[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required'), $field) . $value . ' />';

        $html .= '</div>';

        return $html;
    }

    protected function qUserPhone($field, $id)
    {
        $phone = $this->user->get('phone');
        $value = $phone ? ' value="' . $phone . '"' : '';

        $html = '';
        $html .= '<div class="qf3 qf3txt qftext' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input type="tel" name="qfuserphone[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required'), $field) . $value . ' />';

        $html .= '</div>';

        return $html;
    }

    protected function qInput($field, $id)
    {
        $math = $this->get('math', $field);

        $type = str_replace(array(
                'input[',
                ']'
        ), '', $field->teg);

        $field->fildid = $id . '.' . $field->fildnum;
        $qf3txt = ($type=='button'||$type=='reset')?'qf3btn':'qf3txt';

        $html = '';
        $html .= '<div class="qf3 '.$qf3txt.' qf' . $type . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input type="' . $type . '" name="qf' . $type . '[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required', 'value'), $field);

        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fildid":"' . $field->fildid . '"}') . '"';
        }

        $html .= ' />';
        $html .= '</div>';

        return $html;
    }

    protected function qTextarea($field, $id)
    {
        $html = '';
        $html .= '<div class="qf3 qftextarea' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);
        $html .= '<textarea name="qftextarea[]"' . $this->attr(array('class', 'custom', 'placeholder', 'required'), $field) . '></textarea>';
        $html .= '</div>';

        return $html;
    }

    protected function qSubmit($field, $id)
    {
        $ycounter = $this->get('ycounter', $field);
        $custom = $this->get('custom', $field) ? $field->custom : '';
        $onclick = ' onclick="this.form.submit(this)"';
        if(strpos($custom, 'onclick') !== false) $onclick = '';

        if (!isset($field->value)) {
            $field->value = 'QF_SUBMIT';
        }

        $html = '';
        $html .= '<div class="qf3 qf3btn qfsubmit' . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input name="qfsubmit" type="button"' . $this->attr(array('class', 'custom', 'value'), $field) . $onclick;
        if ($ycounter !== '') {
            $html .= ' data-submit="' . htmlentities('{"ycounter":"' . $ycounter . '"}') . '"';
        }
        $html .= ' />';

        $html .= '</div>';

        return $html;
    }

    protected function qCustomHtml($field, $id)
    {
        if (!isset($field->qfshowf)) {
            $field->qfshowf = 1;
        }

        if ($this->get('qfshowf', $field)) {
            return html_entity_decode($this->mlangLabel($field->label));
        }
    }

    protected function qCalculatorSum($field, $id)
    {
        $html = '';
        $pos = $this->get('pos', $field) ? 1 : 0;
        $unit = $this->get('unit', $field) ? $this->mlangLabel($field->unit) : '';
        $field->fildid = $id . '.' . $field->fildnum;
        $fixed = $this->get('fixed', $field) ? $field->fixed : 0;
        $format = $this->get('format', $field) ? $field->format : 0;
        $datasettings = 'data-settings="' . htmlentities('{"format":"' . $format . '","fixed":"' . $fixed . '","fildid":"' . $field->fildid . '"}') . '"';

        $html .= '<div class="qf3 qfcalculatorsum' . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);
        if ($pos) {
            $html .= '<span class="qfpriceinner" '.$datasettings.'>0</span><span class="qfunitinner">' . $unit . '</span>';
        } else {
            $html .= '<span class="qfunitinner">' . $unit . '</span><span class="qfpriceinner" '.$datasettings.'>0</span>';
        }
        $dat = ' data-unit="'.$unit.'"'; // qfCart
        $html .= '<input name="qfprice[]" type="hidden" value="0"'.$dat.'/>';
        $html .= '</div>';

        return $html;
    }

    protected function qRecaptcha($field, $id)
    {
        $html = '';
        if ($this->user->get('guest') || ! $this->get('show', $field)) {
            $captchaplugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
            $captchaparams = new JRegistry();
            $captchaparams->loadString($captchaplugin->params);
            $pubkey = $captchaparams->get('public_key', '');
            $theme = $captchaparams->get('theme2', 'light');
            if (! $pubkey) {
                return JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY');
            }
            $html .= '<div class="qf3 qfcaptcha' . $this->boxClass($field) . '">';
            $html .= $this->getLabel($field);
            $html .= '<div class="qf_recaptcha" data-sitekey="' . $pubkey . '" data-theme="' . $theme . '" data-hl="' . substr($this->lang->getTag(), 0, 2) . '"></div></div>';
        }
        return $html;
    }

    protected function qBackemail($field, $id)
    {
        if (isset($field->qfshowf) && !$this->get('qfshowf', $field)) {
            return '';
        }
        $reg = $this->get('reg', $field);
        $pos = $this->get('pos', $field);
        $html = '';

        if (!$reg || ($reg && $this->user->get('guest') != 1)) {
            $html .= '<div class="qf3 qfbackemail' . ($this->get('required', $field) ? ' req' : '') . $this->boxClass($field) . '">';
            if ($pos) {
                $html .= '<label class="qf3label qfempty"></label>';
            } else {
                $html .= $this->getLabel($field, 'qfbcemail');
            }
            $html .= '<input id="qfbcemail" name="qfbackemail" type="checkbox" value="1"' . $this->attr(array('class', 'custom', 'required'), $field) . ' />';
            if ($pos) {
                $html .= $this->getLabel($field, 'qfbcemail', 'chbxlabel');
            }
            $html .= '</div>';
        }

        return $html;
    }

    protected function qCloner($field, $id)
    {
        $orient = $this->get('orient', $field);
        $sum = $this->get('sum', $field) ? 1 : 0;
        $max = $this->get('max', $field) ? (int) $field->max : '';
        $related = $this->get('related', $field) ? (int) $field->related : 0;
        $clonerstart = $this->get('clonerstart', $field);
        $clonerend = $this->get('clonerend', $field);
        $field->fildid = $id . '.' . $field->fildnum;

        $html = '';

        if (! $related) {
            return 'qCloner: Field group id not specified.';
        }
        if ($related == $id) {
            return 'recursion error';
        }

        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }

        if (!$orient) {
            $html .= '<div class="qfcloner vertically" data-settings="' . htmlentities('{"orient":"' . $orient . '","sum":"' . $sum . '","max":"' . $max . '","related":"' . $related . '","fildid":"' . $field->fildid . '"}') . '">';
            $html .= '<input type="hidden" name= "qfcloner[]" value="0" data-settings="' . htmlentities('{"math":"' . $clonerstart . '"}') . '" />';
            $html .= '<div class="qfclonerrow">';
            $html .= $this->getFields($data);
            if ($sum) {
                $html .= '<div class="qfclonesum"><span>0</span></div>';
            }
            $html .= '<div class="qfadd"><a href="javascript:void(0)">+</a></div>';
            $html .= '<div class="qfrem"><a href="javascript:void(0)">×</a></div>';
            $html .= '</div>';
            $html .= '<input type="hidden" data-settings="' . htmlentities('{"math":"' . $clonerend . '"}') . '" />';
            $html .= '</div>';
        } else {
            $html .= '<div class="qfcloner horizontally" data-settings="' . htmlentities('{"orient":"' . $orient . '","sum":"' . $sum . '","max":"' . $max . '","related":"' . $related . '","fildid":"' . $field->fildid . '"}') . '">';
            $html .= '<input type="hidden" name= "qfcloner[]" value="0" data-settings="' . htmlentities('{"math":"' . $clonerstart . '"}') . '" />';

            $decodeddata = json_decode($data->fields);

            $html .= '<table>';
            $html .= '<tr>';
            foreach ($decodeddata as $fld) {
                if ($fld->teg != 'customHtml') {
                    $html .= '<th>'.$this->mlangLabel($fld->label).'</th>';
                } else {
                    $html .= '<th></th>';
                }
            }
            if ($sum) {
                $html .= '<th></th>';
            }
            $html .= '<th></th>';
            $html .= '<th></th>';
            $html .= '</tr>';
            $html .= '<tr class="qfclonerrow">';
            foreach ($decodeddata as $fld) {
                unset($fld->label);
                $fld2 = new stdClass;
                $fld2->id = $related;
                $fld2->fields = json_encode(array(0=>$fld));
                $html .= '<td>'.$this->getFields($fld2).'</td>';
            }
            if ($sum) {
                $html .= '<td class="qfclonesum"><span>0</span></td>';
            }
            $html .= '<td class="qfadd"><a href="javascript:void(0)">+</a></td>';
            $html .= '<td class="qfrem"><a href="javascript:void(0)">×</a></td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '<input type="hidden" data-settings="' . htmlentities('{"math":"' . $clonerend . '"}') . '" />';
            $html .= '</div>';
        }

        return $html;
    }

    public function ajaxCloner($id)
    {
        $html = '';
        $data = $this->getDataById($id);
        if (empty($data)) {
            return '';
        }

        $app = JFactory::getApplication();
        $orient = $app->input->get('orient', 0, 'INT');
        $sum = $app->input->get('sum', 0, 'INT');

        $project = $this->getProjectById($data->projectid); // language loading

        if (!$orient) {
            $html .= '<div class="qfclonerrow">';
            $html .= $this->getFields($data);
            if ($sum) {
                $html .= '<div class="qfclonesum"><span>0</span></div>';
            }
            $html .= '<div class="qfadd"><a href="javascript:void(0)">+</a></div>';
            $html .= '<div class="qfrem"><a href="javascript:void(0)">×</a></div>';
            $html .= '</div>';
        } else {
            $decodeddata = json_decode($data->fields);

            $html .= '<tr class="qfclonerrow">';
            foreach ($decodeddata as $fld) {
                unset($fld->label);
                $fld2 = new stdClass;
                $fld2->id = $id;
                $fld2->fields = json_encode(array(0=>$fld));
                $html .= '<td>'.$this->getFields($fld2).'</td>';
            }
            if ($sum) {
                $html .= '<td class="qfclonesum"><span>0</span></td>';
            }
            $html .= '<td class="qfadd"><a href="javascript:void(0)">+</a></td>';
            $html .= '<td class="qfrem"><a href="javascript:void(0)">×</a></td>';
            $html .= '</tr>';
        }
        return $html;
    }

    protected function qQfincluder($field, $id)
    {
        $related = $this->get('related', $field) ? (int) $field->related : 0;
        $start = $this->get('start', $field);
        $end = $this->get('end', $field);
        $condition = $this->get('condition', $field);

        $html = '';

        if (! $related) {
            return 'qfincluder: Field group id not specified.';
        }
        if ($related == $id) {
            return 'recursion error';
        }

        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }

        $html .= '<div class="qQfincluder" data-settings="' . htmlentities('{"condition":"' . $condition . '","istrue":""}') . '">';
        if($condition)$html .= '<input type="hidden" data-settings="' . htmlentities('{"math":"' . $start . '","cond":"1"}') . '" />';
        $html .= $this->getFields($data);
        if($condition)$html .= '<input type="hidden" data-settings="' . htmlentities('{"math":"' . $end . '","cond":"1"}') . '" />';
        $html .= '</div>';

        return $html;
    }

    protected function qAddToCart($field, $id)
    {
        JHtml::_('script', 'modules/mod_qf3/js/qf_cart.js', array('version' => $this->version));

        if (!isset($field->value)) {
            $field->value = 'QF_ADDTOCART';
        }

        $html = '';
        $html .= '<div class="qf3 qfaddtocart' . $this->boxClass($field) . '">';
        $html .= $this->getLabel($field);

        $html .= '<input name="qfaddcart" type="button"' . $this->attr(array('class', 'custom', 'value'), $field) . ' onclick="return this.form.qfaddtocart()" />';

        $html .= '</div>';

        return $html;
    }

    protected function getQFlink()
    {
        $params = JComponentHelper::getParams('com_qf3');
        $cl = '';
        $lang = '';
        if ($params->get('display') == '2' && trim($params->get('cod'))) {
            return '<input name="qfcod" type="hidden" value="' . trim($params->get('cod')) . '" />';
        } elseif ($params->get('display') == '1') {
            $cl = ' nfl';
        }

        if($this->lang->getTag()!='ru-RU') $lang = '/en';

        return '<input name="qfcod" type="hidden" value="" /><div class="qfcapt' . $cl . '"><a href="http://plasma-web.ru'.$lang.'/dev/quickform3" target="_blank">'.JText::_('QF_ACTIVATION').'</a></div>';
    }

    public function ajaxHTML($id)
    {
        $html = '';
        if ($id) {
            $form = $this->getDataById($id);
            if (! empty($form)) {
                $project = $this->getProjectById($form->projectid);
                $html .= $this->getFields($form);
            }
        }
        return $html;
    }
}
