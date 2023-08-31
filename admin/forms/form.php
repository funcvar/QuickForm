<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class Form extends qf_admin
{
    public $xml;
    public $xmlarr;
    public $items;

    public function __construct($tpl, $items)
    {
        $this->items = $items;

        $path = QF3_ADMIN_DIR . 'forms/' . $tpl . '.xml';
        if (is_file($path)) {
            $this->xml = simplexml_load_file($path);
        }
    }

    public function renderForm()
    {
        $html = '';
        $heder = '<div class="qftabslabelsbox">';
        $i = 0;
        foreach ($this->xml->fieldset as $v) {
            $heder .= '<div class="qftabsitemlabel'.(!$i?' qftabactiv':'').'">'.Text::_((string)$v->attributes()->label).'</div>';
            $i++;
            $html .= '<div class="qftabsitem">';
            foreach ($v as $field) {
                $html .= $this->renderField((string)$field['name']);
            }
            $html .= '</div>';

        }
        $heder .= '</div>';
        return ($heder.$html);
    }


    public function renderFieldset($name)
    {
        $fieldset = $this->getfieldset($this->xml, $name);
        $html = '';

        foreach ($fieldset as $field) {
            $html .= $this->renderField((string)$field['name']);
        }
        return $html;
    }

    public function renderField($name)
    {
        $field = $this->XMLToArray()[$name]->attributes();


        $required = $this->get('required', $field) ? ' required' : '';
        $readonly = $this->get('readonly', $field) ? ' readonly' : '';
        $checked = $this->get('checked', $field) ? ' checked' : '';
        $class = $this->get('class', $field) ? ' class="'.$this->get('class', $field).'"' : '';
        $id = $this->get('id', $field) ? ' id="'.$this->get('id', $field).'"' : '';
        $attr = $id.$class.$required.$readonly.$checked;
        $desk = $this->get('description', $field) ? '<div class="desk">'.Text::_($this->get('description', $field)).'</div>' : '';
        $value = $this->get('value', $field);
        if($this->get('translate', $field)) $value = Text::_($value);

        $html = '<div class="qffield box_'.$this->get('class', $field).'">';
        $html .= '<div class="qffieldlabel">' . Text::_($this->get('label', $field)) . ($required ? '<span class="req">*</span>':'') .'</div>';
        $html .= '<div class="qffieldbody">';

        $type = $this->get('type', $field);

        if ($type == 'text') {
            $html .= '<input type="text" name="qffield['.$name.']" value="'.htmlspecialchars($value, ENT_QUOTES).'"'.$attr.'>' . $desk;
        }elseif($type == 'cartimg') {
            if(!isset($this->items->img)) {
                $value = QF3_PLUGIN_URL . 'assets/shopcart/imgs/cart_0.png';
            }
            $desk = '<div class="desk">default: '.QF3_PLUGIN_URL . 'assets/shopcart/imgs/cart_0.png'.'</div>';
            $html .= '<input type="text" name="qffield['.$name.']" value="'.htmlspecialchars($value, ENT_QUOTES).'"'.$attr.'>' . $desk;
        } elseif ($type == 'radio') {
            $options = $this->XMLToArray()[$name];
            $i = 0;

            foreach ($options as $K=>$option) {
                $oattr = $option->attributes();
                $checked = $value == (string)$oattr['value'] ? ' checked' : '';
                $i++;

                $html .= '<label class= "lab'.$i.'"><input type="radio" name="qffield['.$name.']" value="'.$oattr['value'].'"'.$checked.$attr.'>'.Text::_((string)$option).'</label>';
            }
            $html .= $desk;
        } elseif ($type == 'filelist') {
            $path = QF3_PLUGIN_DIR . $this->get('directory', $field);
            if (is_dir($path)) {
                $filter = '#'.$this->get('fileFilter', $field, '(.*).jpg').'#';

                $html .= '<select name="qffield['.$name.']"'.$attr.'>';
                if ($this->get('hide_none', $field) == 'false') {
                    $html .= '<option value="">'.Text::_('QF_NOT_SELECTED').'</option>';
                }

                $fname  = scandir($path);
                for ($i=0, $n=count($fname); $i < $n; $i++) {
                    if (preg_match($filter, $fname[$i])) {
                        $selected = $value == $fname[$i] ? ' selected="selected"' : '';
                        $html .= '<option value="'.$fname[$i].'"'.$selected.'>'.$fname[$i].'</option>';
                    }
                }

                $html .= '</select>';
                $html .= $desk;
            }
        } elseif ($type == 'checkbox') {
            $html .= '<input type="checkbox" name="qffield['.$name.']" value="'.$value.'"'.$attr.'>' . $desk;
        } elseif ($type == 'textarea') {
            $html .= '<textarea name="qffield['.$name.']"'.$attr.'>'.htmlentities($value, ENT_QUOTES).'</textarea>' . $desk;
        } elseif ($type == 'tmpllist') {
            $fname  = scandir(QF3_PLUGIN_DIR.'classes/email');
            $html .= '<select name="qffield['.$name.']"'.$attr.'>';
            for ($i=0, $n=count($fname); $i < $n; $i++) {
                if (substr($fname[$i], strrpos($fname[$i], '.') + 1)=='php') {
                    $tmpl = substr($fname[$i], 0, strrpos($fname[$i], '.'));
                    $selected = $value == $tmpl ? ' selected="selected"' : '';
                    if ($tmpl == 'default') {
                        $html .= '<option value="default"'.$selected.'>default (table output)</option>';
                    } elseif ($tmpl == 'json') {
                        $html .= '<option value="json"'.$selected.'>json (for developers)</option>';
                    } elseif ($tmpl == 'simplehtml') {
                        $html .= '<option value="simplehtml"'.$selected.'>simple (may contain custom html)</option>';
                    } elseif ($tmpl == 'simple') {
                        $html .= '<option value="simple"'.$selected.'>simple (without html)</option>';
                    } else {
                        $html .= '<option value="'.$tmpl.'"'.$selected.'>'.$tmpl.'</option>';
                    }
                }
            }
            $html .= '</select>';
            $html .= $desk;
        } elseif ($type == 'calculatordesk') {
            $html .= '<div class="cdesk cdesk_0"></div><div class="cdesk cdesk_default">';
            $html .= Text::_('QF_CDESK_DEFAULT');
            $html .= '</div><div class="cdesk cdesk_multipl">';
            $html .= Text::_('QF_CDESK_MULTIPL');
            $html .= '</div><div class="cdesk cdesk_simple">';
            $html .= Text::_('QF_CDESK_SIMPLE');
            $html .= '</div><div class="cdesk cdesk_custom">';
            $html .= Text::_('QF_CDESK_CUSTOM');
            $html .= '<pre>
  $customQFcalculator = function($project, $data)
  {
    $r = 0; // cone radius
    $h = 0; // cone height

    foreach($data as $field){
      if(isset($field->math)){
        if($field->math == \'radius\'){
          $r = (float) $field->value;
        }
        elseif($field->math == \'height\'){
          $h = (float) $field->value;
        }
      }
    }

    $l = sqrt($h*$h + $r*$r);
    $calculatorSum[\'42.7\'] = pi()*$r*($l + $r); //  cone area
    $calculatorSum[\'42.8\'] = pi()*$r*$r*$h/3; // cone volume

    return $calculatorSum;
  };

              </pre></div>';
        } elseif ($type == 'accesslevel') {
            $acs = qf::getacs();
            $html .= $this->listfield('access', $acs, $value);
            $html .= $desk;

        } elseif ($type == 'contentlanguage') {
            $languages = $this->getLanguages();
            $html .= $this->listfield('language', $languages, $value);
            $html .= $desk;

        } elseif ($type == 'version') {
            $lang = qf::getlang();
            if ($lang == 'ru_RU') {
                $link = '<a href="http://plasma-web.ru/dev/quickform3" target="_blank">plasma-web.ru</a>';
            } else {
                $link = '<a href="http://plasma-web.ru/en/dev/quickform3" target="_blank">plasma-web.ru</a>';
            }

            $html .=  QF3_VERSION . '<br />url: '.$link;

        } elseif ($type == 'donation') {
            $lang = qf::getlang();
            $code = '<script>var qf_f=formcod.closest(\'form\'),qf_cf=function(){var j=qf_f[\'qffield[display]\'];formcod.closest(\'.qffield\').style.visibility=j[2].checked?\'\':\'hidden\';qflinck.style.visibility=j[2].checked?\'\':\'hidden\'};qf_cf();qf_f.onchange=function(){qf_cf()}</script>';

            $en = $lang == 'ru_RU'?'':'/en';
            $link = '<a href="http://plasma-web.ru'.$en.'/dev/quickform3" target="_blank" id="qflinck">';

            $html .= $link.$code.Text::_('QF_ENTER_CODE').'</a>';

        } elseif ($type == 'recaptcha') {
            $html .=  '<br><br>CAPTCHA: <a href="https://www.google.com/recaptcha" target="_blank">Google reCAPTCHA</a>';

        } elseif ($type == 'spacer') {
            $html .=  '<br><br><br>'.Text::_($value).'<br>';

        } elseif ($type == 'list') {
            $options = $this->XMLToArray()[$name];
            $new = array();

            foreach ($options as $K=>$option) {
                $oattr = $option->attributes();
                $new[(string)$oattr['value']] = (string)$option;
            }
            $html .= $this->listfield($name, $new, $value);
            $html .= $desk;

        } elseif ($type == 'disabledfiles') {
            $dis = 'class="custom-select"';

            if (! qf::conf()->get('filesmod')) {
                $dis = 'class="custom-select" disabled';
                $value = 0;
            }

            $options = array('No', 'QF_ADD_FILES1', 'QF_ADD_FILES2');

            $html .= $this->listfield($name, $options, $value, $dis);
            $html .= $desk;
        } elseif ($type == 'historystatus') {
            require_once(QF3_ADMIN_DIR . 'src/model/historys.php');
            $model = new historysModel();
            $options = $model->statusfields();
            $html .= $this->listfield($name, $options, $value);
        } elseif ($type == 'backcart') {
            $dis = '';

            if (qf::conf()->get('display') != 2 || !qf::gl('key')) {
                $dis = 'disabled';
                $value = 0;
            }

            $options = array('No', 'Yes');
            $html .= $this->listfield($name, $options, $value, $dis);
            $html .= $desk;
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function get($name, $obj, $def='')
    {
        $obj = (object) $obj;
        return isset($obj->$name) ? (string) $obj->$name : $def;
    }

    protected function XMLToArray()
    {
        if (! $this->xmlarr) {
            $this->bind($this->xml);
        }
        return $this->xmlarr;
    }

    protected function bind($xml)
    {
        foreach ($xml as $k => $v) {
            if (is_object($v) && ($k != 'field')) {
                $this->bind($v);
            } elseif ($k == 'field' && isset($v['name'])) {
                $name = (string)$v['name'];
                $def = isset($v['default'])?(string)$v['default']:'';
                $v['value'] = isset($this->items->$name) ? $this->items->$name : $def;
                $this->xmlarr[$name]=$v;
            }
        }
    }

    protected function getfieldset($xml, $name)
    {
        foreach ($xml as $k => $v) {
            if ($v->getName() == 'fieldset' && (string)$v['name'] == $name) {
                return $v;
            }
        }
    }

    protected function listfield($name, $options, $sel, $atr='')
    {
        if($atr) $html = '<select name="qffield['.$name.']"'.$atr.' >';
        else $html = '<select name="qffield['.$name.']" class="'.$name.'" >';

        foreach ($options as $k => $option) {
            $selected = '';
            if ($sel == $k) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="'.$k.'"'.$selected.'>'.Text::_((string)$option).'</option>';
        }

        $html .= '</select>';
        return $html;
    }
}
