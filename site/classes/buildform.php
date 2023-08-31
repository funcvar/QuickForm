<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;
\defined('QF3_VERSION') or die;

class QuickForm
{
  protected $db;
  protected $ajaxquery;
  public $project;

    public function __construct()
    {
        $this->db = \JFactory::getDBO();
        $this->ajaxquery = strpos(qf::get('task', $_POST), 'ajax') === 0;
    }

    public function getShopModule($headonly = true)
    {
        require_once(__DIR__."/qfcart.php");
        $qfcart = new qfCart();

        if(! $this->ajaxquery) {
            if (qf::conf()->get('cartcss', 'shop') != 'none') {
                qf::addScript('css', 'shopcart/'.qf::conf()->get('cartcss', 'shop'));
            }
            $qfcart->checkcss();

            qf::addScript('js', 'js/qf3.js');
            qf::addScript('js', 'shopcart/'.qf::conf()->get('cartjs', 'shop'));
        }

        if(! $headonly) {
            return '<div class="qf_cart_box">'.$qfcart->getMiniCartHtml().'</div>';
        }
    }

    public function checkmessages() {
        $html = '';
        $ses = qf::ses()->get('quickform', []);
        $sproj = qf::get($this->project->id, $ses, []);
        if (! $sproj) return '';

        $err = qf::get('error', $sproj);
        $msg = qf::get('message', $sproj);

        $ses[$this->project->id]['error'] = false;
        $ses[$this->project->id]['message'] = false;
        qf::ses()->set('quickform', $ses);

        if($err || $msg) {
            if (qf::get('cssform', $this->project->params)) {
                $formclass = ' '.str_replace('.css', '', $this->project->params->cssform);
            } else {
                $formclass = '';
            }

            $html .= '<span class="qfhidemes'.$formclass.'" style="display:none"><span class="qfmess">';
            if($err) {
                $html .= '<span class="qfmesserr">' . $err . '</span>';
            }
            if($msg) {
                $html .= '<span class="qfmessmsg">' . $msg . '</span>';
            }
            $html .= '</span></span>';
        }
        return $html;


    }

    public function getQuickForm($id)
    {
        $html = '';
        $project = $this->getProjectById($id);
        $groups = qf::user()->getAuthorisedViewLevels();

        if (! empty($project)) {
            $html .= $this->checkmessages();
            $cssform = qf::get('cssform', $project->params);

            if ($cssform) {
                $formclass = ' '.str_replace('.css', '', $cssform);
            } else {
                $formclass = '';
            }

            if ($project->published && (in_array($project->access, $groups) || ! $project->access)) {
                if ($project->language == qf::getlang() || ! $project->language) {
                    if (! $this->ajaxquery) {

                        if ($cssform) {
                            qf::addScript('css', 'css/'.$cssform);
                        }

                        qf::addScript('js', 'js/'.$project->params->jsform);

                        if (qf::get('modal', $project->params)) {
                            $html .= '<a href="javascript:void(0);" class="qf3modal'.$formclass.'" data-project="'.$project->id.'">'.$project->params->modallink.'</a>';

                            return Text::translate($html);
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
            return 'QuickForm project with id ' . $id . ' not found.';
        }

        if ($html) {
            $rethtml = '';

            $rethtml .=  '<div class="qf3form'.$formclass.'"><form method="post" enctype="multipart/form-data" autocomplete="off">' . $html . '<input name="option" type="hidden" value="com_qf3" /><input name="id" type="hidden" value="' . $id . '" />';
            $rethtml .=  \JHtml::_('form.token');

            $type = qf::get('calculatortype', $project->params);
            if ($type) {
                $rethtml .= '<input name="calculatortype" type="hidden" value="' . $type . '" />';
                if ($type != 'default' && $type != 'custom') {
                    $formula=preg_replace('/\s*\t*/', '', $project->params->calcformula);
                    $rethtml .= '<input name="calcformula" type="hidden" data-formula="' . $formula . '" />';
                }
            }

            if (qf::get('ajaxform', $project->params)) {
                $rethtml .= '<input name="task" type="hidden" value="ajax.qfajax" />';
            } else {
                $rethtml .= '<input name="task" type="hidden" value="qfsubmit" />';
            }

            if (qf::get('qfkeepalive', $project->params)) {
                $rethtml .= '<input type="hidden" value="1" class="qfkeepalive" />';
            }

            $rethtml .= '<input name="root" type="hidden" value="' . qf::getUrl() . '" />' . $this->getQFlink() . '</form></div>';

            return Text::translate($rethtml);
        }
    }


    protected function getDataById($id)
    {
        $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE id = ' . ( int ) $id);
        return $this->db->loadObject();
    }

    public function getProjectById($id)
    {
        $this->db->setQuery('SELECT * FROM #__qf3_projects WHERE id = ' . ( int ) $id);
        $this->project = $this->db->loadObject();

        if ($this->project) {
            $this->project->params = json_decode($this->project->params);
        }

        return $this->project;
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
                $html .= $this->backemail($field);
              break;
              case 'cloner':
                $html .= $this->cloner($field, $id);
              break;
              case 'qfincluder':
                $html .= $this->includer($field, $id);
              break;
              case 'spoiler':
                $html .= $this->spoiler($field, $id);
              break;
              case 'addToCart':
                $html .= $this->addToCart($field, $id);
              break;
              case 'boxadder':
                $html .= $this->boxadder($field, $id);
              break;
              case 'addercart':
                $html .= $this->addercart($field, $id);
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


    protected function getLabel($field, $for = '', $class = 'qf3label')
    {
        if (! isset($field->label)) {
            return '';
        }

        $html = '';
        if ($for) {
            $for = ' for="' . $for . '"';
        }

        if ($field->label) {
            $html .= '<label class="'.$class.'"'.$for.'>' . qf::get('label', $field) . (qf::get('required', $field) ? ' <span class="qf3labelreq">*</span>' : '') . '</label>';
        } else {
            $html .= '<label class="'.$class.'"'.$for.'></label>';
        }
        return $html;
    }

    protected function htmlBox($cl, $field, $html)
    {
        $boxclass = '';
        $fieldclass = qf::get('class', $field);
        if ($fieldclass) {
            $arr = explode(' ', $fieldclass);
            foreach ($arr as $k=>$v) {
                $arr[$k] = ' box_'.$v;
            }
            $boxclass = implode('', $arr);
        }

        return '<div class="'.$cl . (qf::get('required', $field) ? ' req' : '') . $boxclass . '">' . $html . '</div>';
    }

    protected function custom($field)
    {
        $custom = trim(qf::get('custom', $field));
        if ($custom) {
            $custom = ' '.$custom;
        }
        return $custom;
    }


    protected function qInput($field, $id)
    {
        $math = qf::get('math', $field);

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

        $pos = qf::get('pos', $field);
        if ($pos) {
            $html .= '<label class="qf3label"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label filelabel');
        }

        if ($field->teg == 'qf_file') {
            $html .= '<svg width="28" class="customfilebtn" viewBox="0 0 16 16"><g fill="none" fill-rule="evenodd"><path d="M7.703 15.953L6.29 14.54l6.171-6.17c1.768-1.769 1.37-3.75.188-4.932-1.376-1.376-3.31-1.215-4.932.406L2.977 8.581c-1.286 1.286-.28 2.407-.27 2.418.248.25 1.145.975 2.253-.133L10.225 5.6l1.415 1.415-5.266 5.265c-1.91 1.913-4.086 1.128-5.08.133-.545-.544-.903-1.372-.959-2.215-.052-.77.12-1.923 1.228-3.03l4.74-4.739c2.403-2.406 5.594-2.57 7.76-.406 1.88 1.881 2.42 5.151-.189 7.76l-6.17 6.17z" fill="currentColor"></path></g></svg>';
            $html .= '<span class="qfhide"><input type="file" id="' . $rand . '" name="inpfile[][]"' . $this->custom($field) . ' /></span>';
        } else {
            $html .= '<input type="file" id="' . $rand . '" name="inpfile[][]"' . $this->custom($field) . ' />';
        }

        if ($pos) {
            $html .= $this->getLabel($field, $rand, 'filelabel');
            $mesinline = '';
        }

        if ($field->teg == 'qf_file') {
            if ($extens = qf::get('extens', $field)) {
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
        $pos = qf::get('pos', $field);
        $html = '';

        if ($pos) {
            $html .= '<label class="qf3label qfempty"></label>';
        } else {
            $html .= $this->getLabel($field, $rand, 'qf3label qfbefore');
        }

        $html .= '<div class="qfchkbx"><input id="' . $rand . '" type="checkbox" name="chbx[]"' . $this->custom($field);

        if (qf::get('related', $field) || qf::get('math', $field) !== '') {
            if (qf::get('related', $field)) {
                $arr[] = '"related":"' . $field->related . '"';
            }
            if (qf::get('math', $field) !== '') {
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
        if (! qf::get('value', $field)) {
            $custom .= ' value="QF_SUBMIT"';
        }
        if (! qf::get('class', $field)) {
            $custom .= ' class="btn btn-primary"';
        }
        if (! qf::get('onclick', $field)) {
            $custom .= ' onclick="this.form.submit(this)"';
        }
        if ($ycounter = qf::get('ycounter', $field)) {
            $custom .= ' data-submit="' . htmlentities('{"ycounter":"' . $ycounter . '"}') . '"';
        }

        $html = $this->getLabel($field);
        $html .= '<input name="qfsubmit" type="button"' . $custom . ' />';

        return $this->htmlBox('qf3 qf3btn qfsubmit', $field, $html);
    }

    protected function addToCart($field, $id)
    {
        $this->getShopModule();
        $custom = $this->custom($field);
        $tocart = qf::get('value', $field, 'QF_ADDTOCART');
        $custom .= ' value="'.$tocart.'"';
        if (! qf::get('class', $field)) {
            $custom .= ' class="btn btn-primary"';
        }
        if (! qf::get('onclick', $field)) {
            $custom .= ' onclick="return this.form.qfaddtocart()"';
        }

        $html = $this->getLabel($field);
        $html .= '<input name="qfaddcart" type="button"' . $custom . ' data-incart="QF_INCART" data-tocart="'.$tocart.'"/>';

        return $this->htmlBox('qf3 qf3btn qfaddtocart', $field, $html);
    }

    protected function recaptcha($field, $id)
    {
        if (qf::user()->get('guest') || ! qf::conf()->get('recaptcha_show')) {
            $pubkey = qf::conf()->get('sitekey');
            $theme = qf::conf()->get('recaptcha_theme');
            if (! $pubkey) {
                return 'PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY';
            }
            $html = $this->getLabel($field);
            $html .= '<div class="qf_recaptcha" data-sitekey="' . $pubkey . '" data-theme="' . $theme . '" data-hl="' . substr(qf::getlang(), 0, 2) . '"></div>';
            return $this->htmlBox('qf3 qfcaptcha', $field, $html);
        }
        return '';
    }

    protected function calculatorSum($field, $id)
    {
        $unit = qf::get('unit', $field);
        $fixed = qf::get('fixed', $field) ? $field->fixed : 0;
        $datasettings = 'data-settings="' . htmlentities('{"format":"' . qf::get('format', $field) . '","fixed":"' . $fixed . '","fieldid":"' . $field->fieldid . '"}') . '"';

        $html = $this->getLabel($field);
        if (qf::get('pos', $field)) {
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
        $format = qf::get('format', $field) ? $field->format : 'd-m-Y';
        $math = qf::get('math', $field);
        $dis_past = qf::get('past', $field);
        $dis_sa = qf::get('sa', $field);
        $dis_su = qf::get('su', $field);
        $custom = preg_replace("/value\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field)) . ' value="';

        if(! $this->ajaxquery) {
            qf::addScript('css', 'datepicker/css/datepicker.css');
            qf::addScript('js', 'datepicker/js/datepicker.js');
        }

        $html = $this->getLabel($field);

        if (qf::get('double', $field)) {
            if ($val1 = qf::get('val1', $field)) {
                $res = (int) substr($val1, 1);
                if ($val1[0]=='+') {
                    $val1 = date($format, (time()+3600*24*$res));
                } elseif ($val1[0]=='-') {
                    $val1 = date($format, (time()-3600*24*$res));
                }
            } else {
                $val1 = date($format);
            }

            if ($val2 = qf::get('val2', $field)) {
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
                    $html .= '<div class="qf_date_label">'.qf::get('leb1', $field).'</div>';
                    $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'.$custom.$val1.'" /><a href="#" class="qf_date_a"></a></div>';
                    $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
                $html .= '</div>';
                $html .= '<div class="qf_date">';
                    $html .= '<div class="qf_date_label">'.qf::get('leb2', $field).'</div>';
                    $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'.$custom.$val2.'" /><a href="#" class="qf_date_a"></a></div>';
                    $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            if ($val = qf::get('value', $field)) {
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
                        $html .= '<div class="qf_date_inner"><input type="text" name="qfcalendar[]"'. $custom.$val .'" /><a href="#" class="qf_date_a"></a></div>';
                        $html .= '<div class="qf_calen"><div class="widgetCalendar"></div></div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        }

        $params[] = '"format":"' . $format . '"';
        $params[] = '"fieldid":"' . $field->fieldid . '"';
        if ($dis_past) $params[] = '"past":"1"';
        if ($dis_sa) $params[] = '"sa":"1"';
        if ($dis_su) $params[] = '"su":"1"';
        if ($math != '') {
            $params[] = '"math":"' . $math . '"';
        }

        $html .= '<input class="calendar_inp" type="hidden" data-settings="' . htmlentities('{'.implode(',', $params).'}') . '" />';

        return $this->htmlBox('qf3 qfcalendar qf3txt', $field, $html);
    }

    protected function backemail($field)
    {
      if (qf::get('backhide', $field)) {
        return '';
      }

      $guest =qf::user()->get('guest');
      $pos = qf::get('pos', $field);
      $unlogged = qf::conf()->get('unlogged');
      $uniqid = uniqid($field->fieldid);
      $label = qf::get('label', $field);

      if ($guest && $label && ! $unlogged) $field->label .= ' (QF_AUTH_REQ)';

      if ($pos) {
        $html = '<label class="qf3label qfempty"></label>';
      } else {
        $html = $this->getLabel($field, $uniqid);
      }

      if ($guest && ! $unlogged) {
        $html .= '<input id="'.$uniqid.'" name="qfbcemail" type="checkbox"' . $this->custom($field) . ' onclick="return false;" onkeydown="return false;" style="opacity:0.5" />';
      }
      else {
        $html .= '<input id="'.$uniqid.'" name="qfbackemail" type="checkbox" value="1"' . $this->custom($field) . ' />';
      }

      if ($pos) {
        $html .= $this->getLabel($field, $uniqid, 'chbxlabel');
      }

      return $this->htmlBox('qf3 qfbackemail', $field, $html);
    }

    protected function includer($field, $id)
    {
        $related = (int) qf::get('related', $field);

        if (! $related) {
            return 'qfincluder: Fields group id not specified.';
        }
        if ($related == $id) {
            return 'qfincluder: recursion error';
        }

        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }

        return $this->getFields($data);
    }

    protected function spoiler ($field, $id) {
      $related = (int) qf::get('related', $field);
      $spl = (int) qf::get('splr', $field);
      $labelclass = ' closed';
      $closebtn = '';
      if ($spl == 1) $spl = 'hid';
      elseif ($spl == 2) {
        $spl = 'mdl';
        $closebtn = '<div class="qfclose">×</div>';
      }
      else {
        $spl = 'vis';
        $labelclass = ' opened';
      }

      if (! $related) {
          return 'spoiler: Fields group id not specified.';
      }
      if ($related == $id) {
          return 'spoiler: recursion error';
      }

      $data = $this->getDataById($related);
      if (empty($data)) {
          return '';
      }

      $html = $this->getLabel($field, '', 'qf3label'.$labelclass);
      $html .= '<div class="spoilerinner '.$spl.'">'.$closebtn.$this->getFields($data).'</div>';

      return $this->htmlBox('qf3 qfspoiler', $field, $html);
    }

    protected function cloner($field, $id)
    {
        $related = qf::get('related', $field);
        if (! $related) {
            return 'Cloner: Field group id not specified.';
        }
        if ($related == $id) {
            return 'Cloner: recursion error';
        }
        $data = $this->getDataById($related);
        if (empty($data)) {
            return '';
        }
        $html = '';

        $sum = qf::get('sum', $field);
        $clonerstart = qf::get('clonerstart', $field);
        $clonerend = qf::get('clonerend', $field);

        if (! $field->orient) {
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
                $fld2 = new \stdClass;
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
        $class = qf::get('class', $field) ? ' ' . $field->class : '';
        $options = qf::get('options', $field);
        $orient = qf::get('orient', $field) ? ' hor' : ' ver';

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
            $related = qf::get('related', $option);
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
        $cod = qf::get('customphp1', $field);
        if (! $cod) {
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
        $class = qf::get('class', $field) ? ' ' . $field->class : '';
        $related = (int) qf::get('related', $field);

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
        $html .= '<div class="qfstepperinner" style="position:relative">'; // position for preloader
        $html .= $this->getFields($data);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function stepperbtns($field, $id)
    {
        $class = qf::get('class', $field) ? ' ' . $field->class : '';

        $html = '';
        $html .= '<div class="qfstepperbtns' . $class . '">';
        $html .= '<div class="qfprev">'.qf::get('prev', $field).'</div><div class="qfnext" data-next="'.(int) qf::get('related', $field).'">'.qf::get('next', $field).'<input name="qfstepper[]" type="hidden" value="0" /></div>';
        $html .= '</div>';

        return $html;
    }

    protected function customRange($field, $id)
    {
        $orient = qf::get('orient', $field) ? ' ver' : ' hor';
        $math = qf::get('math', $field);
        $min = qf::get('min', $field);
        $max = qf::get('max', $field);

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
        if (! isset($field->qfshowf)) {
            $field->qfshowf = 1;
        }

        if (qf::get('qfshowf', $field)) {
            return html_entity_decode(qf::get('label', $field));
        }
    }

    protected function customNumber($field, $id)
    {
        $orient = qf::get('orient', $field) ? ' hor' : ' ver';
        $math = qf::get('math', $field);

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
        $options = qf::get('options', $field);
        $settings = '';
        $chec = $this->lastChecked($options);

        $html = $this->getLabel($field);

        foreach ($options as $option) {
            if (qf::get('math', $option) != '') {
                $settings = ' data-settings="' . htmlspecialchars('{"fieldid":"' . $field->fieldid . '"}') . '"';
                break;
            }
        }

        $html .= '<select name="qfselect[]"' . $this->custom($field) . $settings.'>';

        $i = '';
        foreach ($options as $option) {
            $arr = array();
            $related = qf::get('related', $option);
            $math = qf::get('math', $option);
            $selected = ((int)$i === $chec)? ' selected':'';

            $html .= '<option value="' . $i . '"' . $selected;

            if ($related || $math !== '') {
                if ($related) {
                    $arr[] = '"related":"' . $related . '"';
                }
                if ($math !== '') {
                    $arr[] = '"math":"' . $math . '"';
                }
                $html .= ' data-settings="' . htmlspecialchars('{'.implode(',', $arr).'}') . '"';
            }

            $html .= '>' . qf::get('label', $option) . '</option>';
            $i ++;
        }

        $html .= '</select>';

        return $this->htmlBox('qf3 qfselect', $field, $html);
    }

    protected function lastChecked($options) {
      $i = 0; $chec = false;
      foreach ($options as $option) {
        if (! $i) $chec = (! isset($option->checked) || $option->checked)? 0:false;
        elseif (qf::get('checked', $option)) $chec = $i;
        $i ++;
      }
      return $chec;
    }

    protected function radio($field, $id)
    {
        $orient = qf::get('orient', $field) ? ' hor' : ' ver';
        $class = qf::get('class', $field);
        $required = qf::get('required', $field);
        $options = qf::get('options', $field);
        $chec = $this->lastChecked($options);

        if ($class) {
            $class0 = explode(' ', $class)[0];
        }

        $html = $this->getLabel($field);
        $html .= '<div class="radioblok">';
        $custom = preg_replace("/(value|class|placeholder|required)\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field));
        $custom = str_replace('required', '', $custom);

        $i = 0;
        $name = str_replace('.', '', uniqid($field->fieldid));

        foreach ($options as $option) {
            $arr = array();
            $related = qf::get('related', $option);
            $math = qf::get('math', $option);
            $img = qf::get('img', $option);
            $checked = ($i === $chec)? ' checked':'';
            $required = !$i && $required ? ' required' : '';

            $html .= '<input type="radio" id="' .$name . $i. '" name="' . $name . '"' . ($class ? ' class="' . $class0.'_'. $i .' '.$class . '"' : '') . $custom . ' value="'.$i.'"' . $required . $checked;

            if ($related || $math !== '') {
                if ($related) {
                    $arr[] = '"related":"' . $related . '"';
                }
                if ($math !== '') {
                    $arr[] = '"math":"' . $math . '"';
                    $arr[] = '"fieldid":"' . $field->fieldid . '"';
                }
                $html .= ' data-settings="{'. htmlspecialchars(implode(',', $arr)) .'}"';
            }

            if ($img) $img = '<img src="'.$img.'">';

            $html .= ' /><label for="' . $name . $i . '"'.($class ? ' class="l_' . $class0.'_'. $i . '"' : '').'>' . $img .qf::get('label', $option) . '</label>';
            $i ++;
        }

        $html .= '<input name="qfradio[]" type="hidden" value="" />';
        $html .= '</div>';

        return $this->htmlBox('qf3 qfradio'.$orient, $field, $html);
    }

    protected function addercart($field, $id) {
      $html = $field->label ? $this->getLabel($field) : '';
      $html .= '<div class="addercartinner">'.$this->adderCartHtml().'</div>';
      return $this->htmlBox('boxaddercart', $field, $html);
    }

    protected function boxadder($field, $id)
    {
        $html = $field->label ? $this->getLabel($field) : '';
        $html .= '<div class="adderblok">';

        $i = 0;
        foreach ($field->options as $option) {
            $img = qf::get('img', $option);
            if ($img) $img = '<img src="'.$img.'">';

            $html .= '<div data-id="'.$field->fieldid.'.'.$i.'"><span>' . $img . '</span><span class="addertitle">' . qf::get('label', $option). '</span>' . qf::formatPrice ($field, $option->math).'<span class="addercntrl"><a class="add" href="javascript:void(0)" onclick="QuickForm.adderadd(this,'.$i.')"> + add</a><a class="added" style="display:none">✔</a></span></div>';
            $i ++;
        }
        $html .= '</div>';

        return $this->htmlBox('boxadder', $field, $html);
    }

    protected function userField($field, $id)
    {
        $type = substr($field->teg, 4);
        $name = qf::user()->get($type);
        if ($type == 'phone') {
            $type = 'tel';
        } elseif ($type == 'name') {
            $type = 'text';
        }
        $value = qf::get('value', $field);
        $custom = preg_replace("/value\s*=\s*[\"][^\"]*[\"]\s?/i", '', $this->custom($field)) . ' value="';
        if ($name && ! $value) {
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

        $orient = (bool) qf::get('orient', $_POST);
        $sum = (bool) qf::get('sum', $_POST);

        if (! $orient) {
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
                $fld2 = new \stdClass;
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
        return Text::translate($html);
    }

    public function adderCartHtml() {
      $ses = qf::ses()->get('quickform', []);
      $rows = qf::get('adder', $ses, []);
      $total = 0;

      if (! $rows) return '<div class="addercartblok empty">'.Text::_('QF_EMPTY_CART').'</div>';

      $html = '<div class="addercartblok">';
      foreach ($rows as $k => $opt) {
        $html .= '<div data-id="'.$k.'">';
        $html .= ($opt->option->img) ? '<span><img src="'.$opt->option->img.'"></span>' : '<span></span>';
        $html .= '<span class="addertitle">' . $opt->option->label. '</span>';
        $html .= '<span><span class="adderqty"><a href="javascript:void(0)" onclick="QuickForm.adderplus(this, \'minus\')">-</a><input type="number" value="' . $opt->option->qty. '" min="1" onchange="QuickForm.adderplus(this, \'change\')"><a href="javascript:void(0)" onclick="QuickForm.adderplus(this, \'plus\')">+</a></span></span>';
        $math = $opt->option->math * $opt->option->qty;
        $html .= qf::formatPrice ($opt, $math);
        $html .= '<span class="addercntrl"><a href="javascript:void(0)" onclick="QuickForm.adderdel(this)">✕</a></span>';
        $html .= '</div>';
        $total += $opt->option->qty * (float) $opt->option->math;
      }

      $html .= '<div class="addertotal">';
      $html .= '<span></span><span class="addertitle"></span>' . qf::formatPrice ($opt, $total, Text::_('QF_TOTAL')) . '<span></span>';
      $html .= '</div>';

      $html .= '</div>';

      return $html;
    }

    public function ajaxAddAdder()
    {
        $opt = qf::gettask('opt');
        $ses = qf::ses()->get('quickform', []);
        if (isset($ses['adder'][$opt])) return $this->adderCartHtml();

        $nums = explode('.',$opt);
        if (sizeof($nums) !== 3) return;

        $data = $this->getDataById($nums[0]);
        if (empty($data)) return;

        $fields = json_decode($data->fields);

        if (isset($fields[$nums[1]])) {
          $field = $fields[$nums[1]];
          $field->option = $field->options[$nums[2]];
          unset($field->options);
          $field->option->qty = 1;
          $ses['adder'][$opt] = $field;
          qf::ses()->set('quickform', $ses);
          return $this->adderCartHtml();
        }
    }

    public function ajaxDelAdder() {
      $opt = qf::gettask('opt');
      $ses = qf::ses()->get('quickform', []);
      unset($ses['adder'][$opt]);
      qf::ses()->set('quickform', $ses);
      return $this->adderCartHtml();
    }

    public function ajaxPlusAdder() {
      $opt = qf::gettask('opt');
      $act = qf::gettask('act');
      $ses = qf::ses()->get('quickform', []);
      if ($act == 'plus') $ses['adder'][$opt]->option->qty ++;
      elseif ($act == 'minus') $ses['adder'][$opt]->option->qty --;
      elseif ($act == 'change') $ses['adder'][$opt]->option->qty = (int) qf::gettask('v');
      if ($ses['adder'][$opt]->option->qty < 1) $ses['adder'][$opt]->option->qty = 1;
      qf::ses()->set('quickform', $ses);
      return $this->adderCartHtml();
    }

    protected function getQFlink()
    {
        $cl = '';
        $lang = '';
        if (qf::conf()->get('display') == '2' && trim(qf::conf()->get('cod'))) {
            return '<input name="qfcod" type="hidden" value="' . trim(qf::conf()->get('cod')) . '" />';
        } elseif (qf::conf()->get('display') == '1') {
            $cl = ' nfl';
        }

        if (qf::getlang() != 'ru_RU') {
            $lang = '/en';
        }

        return '<input name="qfcod" type="hidden" value="" /><div class="qfcapt' . $cl . '"><a href="http://plasma-web.ru'.$lang.'/dev/quickform3" target="_blank">QF_ACTIVATION</a></div>';
    }

    public function ajaxHTML($id)
    {
        if ($id) {
            $form = $this->getDataById($id);
            if (! empty($form)) {
                return Text::translate($this->getFields($form));
            }
        }
        return '';
    }
}
