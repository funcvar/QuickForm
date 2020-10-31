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

    public function getShopModule($modparams)
    {
        JHtml::_('jquery.framework');

        if ($modparams->get('cartcss') != 'none') {
            JHtml::_('stylesheet', 'modules/mod_qf3/assets/css/' . $modparams->get('cartcss'), array('version' => $this->version));
        }

        JHtml::_('script', 'components/com_qf3/assets/js/qf3.js', array('version' => $this->version));
        JHtml::_('script', 'modules/mod_qf3/assets/js/qf_cart.js', array('version' => $this->version));

        require_once("components/com_qf3/classes/qfcart.php");
        $qfcart = new qfCart();
        return '<div class="qf_cart_box">'.$qfcart->getMiniCartHtml().'</div>';
    }


    public function getQuickForm($id)
    {
        $html = '';
        $app = JFactory::getApplication();
        $groups = $this->user->getAuthorisedViewLevels();

        $ajaxquery = $app->input->get('task')=='ajax';
        $project = $this->getProjectById($id);

        if (! empty($project)) {
            if ($project->formparams->cssform != -1) {
                $formclass = ' '.str_replace(array('.css','-1'), '', $project->formparams->cssform);
            } else {
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
                            $html .= '<a href="javascript:void(0);" class="qf3modal" data-project="'.$project->id.'" data-url="'.JURI::current().'" data-class="'.trim($formclass).'">'.$project->formparams->modallink.'</a>';

                            return $this->translate($html);
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

            return $this->translate($rethtml);
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
            $field->fieldid = $id . '.' . $field->fildnum;

            switch ($field->teg) {
              case 'select':
                $html .= $this->select($field, $id);
              break;
              case 'input[radio]':
                $html .= $this->radio($field, $id);
              break;
              case 'input[checkbox]':
              case 'qf_checkbox':
                $html .= $this->customCheckbox($field, $id);
              break;
              case 'username':
              case 'userphone':
              case 'useremail':
                $html .= $this->userField($field, $id);
              break;
              case 'input[file]':
              case 'qf_file':
                $html .= $this->customFile($field, $id);
              break;
              case 'textarea':
                $html .= $this->textarea($field, $id);
              break;
              case 'submit':
                $html .= $this->submit($field, $id);
              break;
              case 'customHtml':
                $html .= $this->customHtml($field, $id);
              break;
              case 'customPhp':
                $html .= $this->customPhp($field, $id);
              break;
              case 'calculatorSum':
                $html .= $this->calculatorSum($field, $id);
              break;
              case 'recaptcha':
                $html .= $this->recaptcha($field, $id);
              break;
              case 'backemail':
                $html .= $this->backemail($field, $id);
              break;
              case 'cloner':
                $html .= $this->cloner($field, $id);
              break;
              case 'qfincluder':
                $html .= $this->includer($field, $id);
              break;
              case 'addToCart':
                $html .= $this->addToCart($field, $id);
              break;
              case 'qftabs':
                $html .= $this->qfTabs($field, $id);
              break;
              case 'qf_number':
                $html .= $this->customNumber($field, $id);
              break;
              case 'qf_range':
                $html .= $this->customRange($field, $id);
              break;
              case 'qfcalendar':
                $html .= $this->customCalendar($field, $id);
              break;
              case 'stepperbox':
                $html .= $this->stepperbox($field, $id);
              break;
              case 'stepperbtns':
                $html .= $this->stepperbtns($field, $id);
              break;
              default:
                $html .= $this->qInput($field, $id);
          }
        }

        return $html;
    }

    protected function translate($text)
    {
        return preg_replace_callback('/\b(QF_\w+)\b/', function ($m) {
            return JText::_($m[1]);
        }, $text);
    }


    protected function get($v, $obj, $def = '')
    {
        $obj = (object)$obj;
        if (!isset($obj->$v)) {
            if (isset($obj->custom) && strpos($obj->custom, $v) !== false) {
                $pattern = "/".$v."\s*=\s*[\"]([^\"]*)[\"]\s?/i";
                preg_match($pattern, $obj->custom, $m);
                if (isset($m[1])) {
                    return $m[1];
                } else {
                    $subject = preg_replace("/\s*=\s*[\"]([^\"]*)[\"]\s?/i", '', $obj->custom);
                    if (strpos($subject, $v) !== false) {
                        return true;
                    } else {
                        return $def;
                    }
                }
            }
            return $def;
        }
        return ($obj->$v) ? $obj->$v : $def;
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
            $html .= '<label class="'.$class.'"'.$for.'>' . $this->get('label', $field) . ($this->get('required', $field) ? ' <span class="qf3labelreq">*</span>' : '') . '</label>';
        } else {
            $html .= '<label class="'.$class.'"'.$for.'></label>';
        }
        return $html;
    }

    protected function htmlBox($cl, $field, $html)
    {
        $boxclass = '';
        $fieldclass = $this->get('class', $field);
        if ($fieldclass) {
            $arr = explode(' ', $fieldclass);
            foreach ($arr as $k=>$v) {
                $arr[$k] = ' box_'.$v;
            }
            $boxclass = implode('', $arr);
        }

        return '<div class="'.$cl . ($this->get('required', $field) ? ' req' : '') . $boxclass . '">' . $html . '</div>';
    }

    protected function custom($field)
    {
        $custom = trim($this->get('custom', $field));
        if ($custom) {
            $custom = ' '.$custom;
        }
        return $custom;
    }


    protected function qInput($field, $id)
    {
        $math = $this->get('math', $field);

        $type = str_replace(array(
                'input[',
                ']'
        ), '', $field->teg);

        $qf3txt = ($type=='button'||$type=='reset')?'qf3btn':'qf3txt';

        $html = $this->getLabel($field);

        $html .= '<input type="' . $type . '" name="qf' . $type . '[]"' . $this->custom($field);
        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fieldid":"' . $field->fieldid . '"}') . '"';
        }
        $html .= ' />';

        return $this->htmlBox('qf3 '.$qf3txt.' qf'.$type, $field, $html);
    }


    protected function customFile($field, $id)
    {
        $html = '';
        $mesinline = ' qfinline';
        $rand = str_replace('.', '', microtime(1) . $field->fieldid);

        $pos = $this->get('pos', $field);
        if ($pos) {
            $html .= '<label class="qf3label"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label filelabel');
        }

        if ($field->teg == 'qf_file') {
            $html .= '<svg class="customfilebtn" viewBox="0 0 16 16"><g fill="none" fill-rule="evenodd"><path d="M7.703 15.953L6.29 14.54l6.171-6.17c1.768-1.769 1.37-3.75.188-4.932-1.376-1.376-3.31-1.215-4.932.406L2.977 8.581c-1.286 1.286-.28 2.407-.27 2.418.248.25 1.145.975 2.253-.133L10.225 5.6l1.415 1.415-5.266 5.265c-1.91 1.913-4.086 1.128-5.08.133-.545-.544-.903-1.372-.959-2.215-.052-.77.12-1.923 1.228-3.03l4.74-4.739c2.403-2.406 5.594-2.57 7.76-.406 1.88 1.881 2.42 5.151-.189 7.76l-6.17 6.17z" fill="currentColor"></path></g></svg>';
            $html .= '<span class="qfhide"><input type="file" id="' . $rand . '" name="inpfile[][]"' . $this->custom($field) . ' /></span>';
        } else {
            $html .= '<input type="file" id="' . $rand . '" name="inpfile[][]"' . $this->custom($field) . ' />';
        }

        if ($pos) {
            $html .= $this->getLabel($field, $rand, 'filelabel');
            $mesinline = '';
        }

        if ($field->teg == 'qf_file') {
            if ($extens = $this->get('extens', $field)) {
                $extens = '('.$extens.') ';
            }
            $html .= '<div class="customfilemes'.$mesinline.'">'.$extens.'max: ' . get_cfg_var('upload_max_filesize').'</div>';
            $html .= '<div class="customfilebox"></div>';
            return $this->htmlBox('qf3 customfile', $field, $html);
        } else {
            return $this->htmlBox('qf3 qffile', $field, $html);
        }
    }

    protected function customCheckbox($field, $id)
    {
        $class = $field->teg == 'qf_checkbox'?'qf_checkbox':'qfcheckbox';
        $rand = str_replace('.', '', microtime(1) . $field->fieldid);
        $pos = $this->get('pos', $field);
        $html = '';

        if ($pos) {
            $html .= '<label class="qf3label qfempty"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label qfbefore');
        }

        $html .= '<div class="qfchkbx"><input id="' . $rand . '" type="checkbox" name="chbx"' . $this->custom($field);

        if ($this->get('related', $field) || $this->get('math', $field) !== '') {
            if ($field->related) {
                $arr[] = '"related":"' . $field->related . '"';
            }
            if ($field->math !== '') {
                $arr[] = '"math":"' . $field->math . '"';
            }
            $arr[] = '"fieldid":"' . $field->fieldid . '"';
            $html .= ' data-settings="' . htmlentities('{'.implode(',', $arr).'}') . '"';
        }
        $html .= ' />';

        if ($pos) {
            $html .= $this->getLabel($field, $rand, 'chbxlabel');
        } elseif ($class == 'qf_checkbox') {
            $field->label = '';
            $html .= $this->getLabel($field, $rand, 'chbxlabel');
        }

        $html .= '<input name="qfcheckbox[]" type="hidden" value="0" />';
        $html .= '</div>';

        return $this->htmlBox('qf3 '.$class, $field, $html);
    }

    protected function submit($field, $id)
    {
        $custom = $this->custom($field);
        if (!$this->get('value', $field)) {
            $custom .= ' value="'.JText::_('QF_SUBMIT').'"';
        }
        if (!$this->get('class', $field)) {
            $custom .= ' class="btn btn-primary"';
        }
        if (!$this->get('onclick', $field)) {
            $custom .= ' onclick="this.form.submit(this)"';
        }
        if ($ycounter = $this->get('ycounter', $field)) {
            $custom .= ' data-submit="' . htmlentities('{"ycounter":"' . $ycounter . '"}') . '"';
        }

        $html = $this->getLabel($field);
        $html .= '<input name="qfsubmit" type="button"' . $custom . ' />';

        return $this->htmlBox('qf3 qf3btn qfsubmit', $field, $html);
    }

    protected function addToCart($field, $id)
    {
        JHtml::_('script', 'modules/mod_qf3/js/qf_cart.js', array('version' => $this->version));
        $custom = $this->custom($field);
        if (!$this->get('value', $field)) {
            $custom .= ' value="'.JText::_('QF_ADDTOCART').'"';
        }
        if (!$this->get('class', $field)) {
            $custom .= ' class="btn btn-primary"';
        }
        if (!$this->get('onclick', $field)) {
            $custom .= ' onclick="return this.form.qfaddtocart()"';
        }

        $html = $this->getLabel($field);
        $html .= '<input name="qfaddcart" type="button"' . $custom . ' />';

        return $this->htmlBox('qf3 qf3btn qfaddtocart', $field, $html);
    }

    protected function recaptcha($field, $id)
    {
        $params = JComponentHelper::getParams('com_qf3');
        if ($this->user->get('guest') || ! $params->get('recaptcha_show')) {
            $pubkey = $params->get('sitekey', '');
            $theme = $params->get('theme', 'light');
            if (! $pubkey) {
                return JText::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY');
            }
            $html = $this->getLabel($field);
            $html .= '<div class="qf_recaptcha" data-sitekey="' . $pubkey . '" data-theme="' . $theme . '" data-hl="' . substr($this->lang->getTag(), 0, 2) . '"></div>';
            return $this->htmlBox('qf3 qfcaptcha', $field, $html);
        }
        return '';
    }

    protected function calculatorSum($field, $id)
    {
        $unit = $this->get('unit', $field);
        $fixed = $this->get('fixed', $field) ? $field->fixed : 0;
        $datasettings = 'data-settings="' . htmlentities('{"format":"' . $this->get('format', $field) . '","fixed":"' . $fixed . '","fieldid":"' . $field->fieldid . '"}') . '"';

        $html = $this->getLabel($field);
        if ($this->get('pos', $field)) {
            $html .= '<span class="qfpriceinner" '.$datasettings.'>0</span><span class="qfunitinner">' . $unit . '</span>';
        } else {
            $html .= '<span class="qfunitinner">' . $unit . '</span><span class="qfpriceinner" '.$datasettings.'>0</span>';
        }
        $dat = ' data-unit="'.$unit.'"'; // qfCart
        $html .= '<input name="qfprice[]" type="hidden" value="0"'.$dat.'/>';

        return $this->htmlBox('qf3 qfcalculatorsum', $field, $html);
    }

    protected function customCalendar($field, $id)
    {
        $format = $this->get('format', $field) ? $field->format : 'd-m-Y';
        $math = $this->get('math', $field);
        $custom = preg_replace("/value\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field)) . ' value="';

        JHtml::_('stylesheet', 'components/com_qf3/assets/datepicker/css/datepicker.css', array('version' => $this->version));
        JHtml::_('script', 'components/com_qf3/assets/datepicker/js/datepicker.js', array('version' => $this->version));

        $html = $this->getLabel($field);

        if ($this->get('double', $field)) {
            if ($val1 = $this->get('val1', $field)) {
                $res = (int) substr($val1, 1);
                if ($val1[0]=='+') {
                    $val1 = date($format, (time()+3600*24*$res));
                } elseif ($val1[0]=='-') {
                    $val1 = date($format, (time()-3600*24*$res));
                }
            } else {
                $val1 = date($format);
            }

            if ($val2 = $this->get('val2', $field)) {
                $res = (int) substr($val2, 1);
                if ($val2[0]=='+') {
                    $val2 = date($format, (time()+3600*24*$res));
                } elseif ($val2[0]=='-') {
                    $val2 = date($format, (time()-3600*24*$res));
                }
            } else {
                $val2 = date($format);
            }


            $html .= '<div class="double">';
            $html .= '<div class="double_inner">';
            $html .= '<div class="qf_date">';
            $html .= '<div class="qf_date_label">'.$this->get('leb1', $field).'</div>';
            $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'.$custom.$val1.'" /><a href="#" class="qf_date_a"></a></div>';
            $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
            $html .= '<div class="qf_date">';
            $html .= '<div class="qf_date_label">'.$this->get('leb2', $field).'</div>';
            $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'.$custom.$val2.'" /><a href="#" class="qf_date_a"></a></div>';
            $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            if ($val = $this->get('value', $field)) {
                $res = (int) substr($val, 1);
                if ($val[0]=='+') {
                    $val = date($format, (time()+3600*24*$res));
                } elseif ($val[0]=='-') {
                    $val = date($format, (time()-3600*24*$res));
                }
            } else {
                $val = date($format);
            }

            $html .= '<div class="single">';
            $html .= '<div class="single_inner">';
            $html .= '<div class="qf_date">';
            $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'.$custom.$val.'" /><a href="#" class="qf_date_a"></a></div>';
            $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $params[] = '"format":"' . $format . '"';
        $params[] = '"fieldid":"' . $field->fieldid . '"';
        if ($math != '') {
            $params[] = '"math":"' . $math . '"';
        }

        $html .= '<input class="calendar_inp" type="hidden" data-settings="' . htmlentities('{'.implode($params, ',').'}') . '" />';

        return $this->htmlBox('qf3 qfcalendar qf3txt', $field, $html);
    }

    protected function backemail($field, $id)
    {
        if (isset($field->qfshowf) && !$this->get('qfshowf', $field)) {
            return '';
        }
        $reg = $this->get('reg', $field);
        $pos = $this->get('pos', $field);

        if (!$reg || ($reg && $this->user->get('guest') != 1)) {
            if ($pos) {
                $html = '<label class="qf3label qfempty"></label>';
            } else {
                $html = $this->getLabel($field, 'qfbcemail');
            }
            $html .= '<input id="qfbcemail" name="qfbackemail" type="checkbox" value="1"' . $this->custom($field) . ' />';
            if ($pos) {
                $html .= $this->getLabel($field, 'qfbcemail', 'chbxlabel');
            }
            return $this->htmlBox('qf3 qfbackemail', $field, $html);
        }

        return '';
    }

    protected function includer($field, $id)
    {
        $related = (int) $this->get('related', $field);

        if (! $related) {
            return 'qfincluder: Fields group id not specified.';
        }
        if ($related == $id) {
            return 'recursion error';
        }

        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }

        return $this->getFields($data);
    }

    protected function cloner($field, $id)
    {
        $related = $this->get('related', $field);
        if (! $related) {
            return 'Cloner: Field group id not specified.';
        }
        if ($related == $id) {
            return 'recursion error';
        }
        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }
        $html = '';

        $sum = $this->get('sum', $field);
        $clonerstart = $this->get('clonerstart', $field);
        $clonerend = $this->get('clonerend', $field);

        if (!$field->orient) {
            $html .= '<div class="qfcloner ver" data-settings="' . htmlentities('{"orient":"' . $field->orient . '","sum":"' . $sum . '","max":"' .  $field->max . '","related":"' . $related . '","fieldid":"' . $field->fieldid . '"}') . '">';
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
            $html .= '<div class="qfcloner hor" data-settings="' . htmlentities('{"orient":"' . $field->orient . '","sum":"' . $sum . '","max":"' .  $field->max . '","related":"' . $related . '","fieldid":"' . $field->fieldid . '"}') . '">';
            $html .= '<input type="hidden" name= "qfcloner[]" value="0" data-settings="' . htmlentities('{"math":"' . $clonerstart . '"}') . '" />';

            $decodeddata = json_decode($data->fields);

            $html .= '<table>';
            $html .= '<tr>';
            foreach ($decodeddata as $fld) {
                if ($fld->teg != 'customHtml') {
                    $html .= '<th>'.$fld->label.'</th>';
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

    protected function qfTabs($field, $id)
    {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';
        $options = $this->get('options', $field);
        $orient = $this->get('orient', $field) ? ' hor' : ' ver';

        $html = '';

        if ($field->label) {
            $html .= '<div class="qftabslabel">' . $field->label . '</div>';
        }
        $html .= '<div class="qftabs' . $orient . $class . '">';

        $html .= '<div class="qftabslabelsbox">';
        $i = 0;
        foreach ($options as $option) {
            $additionalclass = ($i%2)?' qfodd':' qfeven';
            $activ = ($i==0)?' qftabactiv':'';

            $html .= '<div class="qftabsitemlabel'.$additionalclass.$activ.'">' . $option->label . '</div>';
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

    protected function customPhp($field, $id)
    {
        $cod = $this->get('customphp1', $field);
        if (!$cod) {
            return '';
        }
        $res = '';
        $tmpfname = tempnam(sys_get_temp_dir(), "qf");
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

    protected function stepperbox($field, $id)
    {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';
        $related = (int) $this->get('related', $field);

        if (! $related) {
            return 'Stepper: Field group id not specified.';
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
            $html .= '<div class="qfstepperlabel">' . $field->label . '</div>';
        }
        $html .= '<div class="qfstepper' . $class . '">';
        $html .= '<div class="qfstepperinner">';
        $html .= $this->getFields($data);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function stepperbtns($field, $id)
    {
        $class = $this->get('class', $field) ? ' ' . $field->class : '';

        $html = '';
        $html .= '<div class="qfstepperbtns' . $class . '">';
        $html .= '<div class="qfprev">'.$this->get('prev', $field).'</div><div class="qfnext" data-next="'.(int) $this->get('related', $field).'">'.$this->get('next', $field).'<input name="qfstepper[]" type="hidden" value="0" /></div>';
        $html .= '</div>';

        return $html;
    }

    protected function customRange($field, $id)
    {
        $orient = $this->get('orient', $field) ? ' ver' : ' hor';
        $math = $this->get('math', $field);
        $min = $this->get('min', $field);
        $max = $this->get('max', $field);

        $html = $this->getLabel($field);
        $html .= '<div class="qfslider_inner">';
        $html .= '<div class="slider_min">'.($min?$min:'0').'</div><div class="slider_chosen"></div><div class="slider_max">'.($max?$max:'100').'</div>';

        $html .= '<input type="range" name="qfrange[]"'.$this->custom($field);
        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fieldid":"' . $field->fieldid . '"}') . '"';
        }
        $html .= ' />';

        $html .= '</div>';

        return $this->htmlBox('qf3 qfslider'.$orient, $field, $html);
    }

    protected function customHtml($field, $id)
    {
        if (!isset($field->qfshowf)) {
            $field->qfshowf = 1;
        }

        if ($this->get('qfshowf', $field)) {
            return html_entity_decode($this->get('label', $field));
        }
    }

    protected function customNumber($field, $id)
    {
        $orient = $this->get('orient', $field) ? ' hor' : ' ver';
        $math = $this->get('math', $field);

        $html = $this->getLabel($field);
        $html .= '<div class="qf_number_inner">';

        $html .= '<input type="number" name="qfnumber[]"'.$this->custom($field);
        if ($math !== '') {
            $html .= ' data-settings="' . htmlentities('{"math":"' . $math . '","fieldid":"' . $field->fieldid . '"}') . '"';
        }
        $html .= ' />';

        $html .= '<div class="number__controls"><button type="button" class="qfup">+</button><button type="button" class="qfdown">−</button></div>';
        $html .= '</div>';

        return $this->htmlBox('qf3 qf_number'.$orient, $field, $html);
    }

    protected function select($field, $id)
    {
        $options = $this->get('options', $field);
        $settings = '';

        $html = $this->getLabel($field);

        foreach ($options as $option) {
            if ($this->get('math', $option) != '') {
                $settings = ' data-settings="' . htmlentities('{"fieldid":"' . $field->fieldid . '"}') . '"';
                break;
            }
        }

        $html .= '<select name="qfselect[]"' . $this->custom($field) . $settings.'>';

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

            $html .= '>' . $this->get('label', $option) . '</option>';
            $i ++;
        }

        $html .= '</select>';

        return $this->htmlBox('qf3 qfselect', $field, $html);
    }

    protected function radio($field, $id)
    {
        $orient = $this->get('orient', $field) ? ' hor' : ' ver';
        $class = $this->get('class', $field);
        $required = $this->get('required', $field);

        if ($class) {
            $class2 = explode(' ', $class)[0];
        }

        $html = $this->getLabel($field);
        $html .= '<div class="radioblok">';
        $custom = preg_replace("/(value|class|placeholder|required)\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field));
        $custom = str_replace('required', '', $custom);

        $i = 0;
        $name = str_replace('.', '', microtime(1) . $field->fieldid);

        foreach ($field->options as $option) {
            $arr = array();
            $related = $this->get('related', $option);
            $math = $this->get('math', $option);

            $html .= '<input type="radio" id="' .$name . $i. '" name="' . $name . '"' . ($class ? ' class="' . $class2.'_'. $i .' '.$class . '"' : '') . $custom . ' value="' .$i. '"' .(!$i&&!$required?' checked':'') . (!$i&&$required?' required':'');

            if ($related || $math !== '') {
                if ($related) {
                    $arr[] = '"related":"' . $related . '"';
                }
                if ($math !== '') {
                    $arr[] = '"math":"' . $math . '"';
                    $arr[] = '"fieldid":"' . $field->fieldid . '"';
                }
                $html .= ' data-settings="' . htmlentities('{'.implode(',', $arr).'}') . '"';
            }

            $html .= ' /><label for="' . $name . $i . '"'.($class ? ' class="l_' . $class2.'_'. $i . '"' : '').'>' . $this->get('label', $option) . '</label>';
            $i ++;
        }

        $html .= '<input name="qfradio[]" type="hidden" value="0" />';
        $html .= '</div>';

        return $this->htmlBox('qf3 qfradio'.$orient, $field, $html);
    }

    protected function userField($field, $id)
    {
        $type = substr($field->teg, 4);
        $name = $this->user->get($type);
        if ($type == 'phone') {
            $type = 'tel';
        } elseif ($type == 'name') {
            $type = 'text';
        }
        $value = $this->get('value', $field);
        $custom = preg_replace("/value\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field)) . ' value="';
        if ($name && !$value) {
            $value = $name;
        }

        $html = $this->getLabel($field);
        $html .= '<input type="'.$type.'" name="qf'.$field->teg.'[]"' . $custom . $value . '" />';

        return $this->htmlBox('qf3 qf3txt qftext', $field, $html);
    }

    protected function textarea($field, $id)
    {
        $html = $this->getLabel($field);
        $html .= '<textarea name="qftextarea[]"' . $this->custom($field) . '></textarea>';
        return $this->htmlBox('qf3 qftextarea', $field, $html);
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
        return $this->translate($html);
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

        if ($this->lang->getTag()!='ru-RU') {
            $lang = '/en';
        }

        return '<input name="qfcod" type="hidden" value="" /><div class="qfcapt' . $cl . '"><a href="http://plasma-web.ru'.$lang.'/dev/quickform3" target="_blank">QF_ACTIVATION</a></div>';
    }

    public function ajaxHTML($id)
    {
        if ($id) {
            $form = $this->getDataById($id);
            if (! empty($form)) {
                $project = $this->getProjectById($form->projectid);
                return $this->translate($this->getFields($form));
            }
        }
        return '';
    }
}
