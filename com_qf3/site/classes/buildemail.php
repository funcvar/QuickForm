<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

require_once(__DIR__."/calculator.php");
require_once(__DIR__."/email.php");

class qfFilds
{
    public $submited = false;
    public $calculated = false;
    public $iscart = false;
    public $errormes = array();
    public $project = false;
    public $redirect = false;
    protected $child = array();
    public $back = false;
    protected $stepperdata = array();
    protected $fileListToEmail = array();
    public $fileListToServer = array();

    public function __construct()
    {
        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDBO();
        $this->user = JFactory::getUser();
        $this->ajaxform = $this->app->input->get('task')=='ajax';
        $this->qf_params = JComponentHelper::getParams('com_qf3');
    }

    public function getResultHtml($project)
    {
        $data = $this->getData($project->id);
        $project->calculated = $this->calculated && $project->calculatorparams->calculatortype;
        $calculator = qfCalculator::getCalculator($project, $data);
        $html = qfEmail::getEmailHtml($project, $data, $calculator);
        return $this->translate($html);
    }

    public function sumCustomAjax()
    {
        $strarr = array();
        $id = $this->app->input->get('id', 0, 'int');
        $project = $this->getProjectById($id);
        if (!$project) {
            return '';
        }
        $data = $this->getData($project->id);
        $project->calculated = $this->calculated && $project->calculatorparams->calculatortype;
        $sumarr = qfCalculator::getCalculator($project, $data);
        foreach ($sumarr as $arr) {
            $strarr[] = $arr[1]->fieldid . ':' . $arr[0];
        }
        return implode(';', $strarr);
    }

    public function getErrormes()
    {
        $err = qfCalculator::qfErrormes();
        return array_merge($this->errormes, $err);
    }

    public function translate($text)
    {
        return preg_replace_callback('/\b(QF_\w+)\b/', function ($m) {
            return JText::_($m[1]);
        }, $text);
    }

    public function qfcheckToken()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $token = explode('/', str_replace(array('w', '.', '-', '|'), '', $this->app->input->get('root', '', 'string')));
        if ($token[2] != $this->app->input->get('qftoken', '', 'string')) {
            jexit(JText::_('JINVALID TOKEN'));
        }
    }

    public function getProjectById($id)
    {
        if ($this->project) {
            return $this->project;
        }

        $lang = JFactory::getLanguage();
        $groups = implode(',', $this->user->getAuthorisedViewLevels());
        $this->db->setQuery('SELECT * FROM #__qf3_projects WHERE published=1 AND (language=' . $this->db->quote($lang->getTag()) . ' OR language="*") AND access IN (' . $groups . ') AND id = ' . ( int ) $id);
        $this->project = $this->db->loadObject();

        if (empty($this->project)) {
            return false;
        }

        $this->project->params = json_decode($this->project->params);
        $this->project->formparams = json_decode($this->project->formparams);
        $this->project->emailparams = json_decode($this->project->emailparams);
        $this->project->calculatorparams = json_decode($this->project->calculatorparams);

        if ($this->project->params->languagelink) {
            $lang->load($this->project->params->languagelink);
        }

        return $this->project;
    }

    public function getData($projectid)
    {
        $data = array();

        $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE def=1 AND projectid = ' . ( int ) $projectid);
        $form = $this->db->loadObject();
        if (! empty($form)) {
            $data = $this->getFields($form);
        }
        return $data;
    }

    protected function getChildren($id)
    {
        if (! isset($this->child [(int) $id])) {
            $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE id = ' . (int) $id);
            $this->child [(int) $id] = $this->db->loadObject();
        }

        return $this->getFields($this->child [(int) $id]);
    }

    protected function getFields($form)
    {
        if (! $form) {
            return array();
        }
        $data = array();
        $fields = json_decode($form->fields);

        foreach ($fields as $field) {
            $field->fieldid = $form->id . '.' . $field->fildnum;
            unset($field->fildnum);

            if (!isset($field->hide)) {
                $field->hide = 0;
            }

            switch ($field->teg) {
              case 'select':
                $data [] = $this->select($field);
              break;
              case 'input[radio]':
                $data [] = $this->radio($field);
              break;
              case 'input[checkbox]':
              case 'qf_checkbox':
                $data [] = $this->checkbox($field);
              break;
              case 'textarea':
                $data [] = $this->textarea($field);
              break;
              case 'customHtml':
                  $data [] = $this->customHtml($field);
              break;
              case 'customPhp':
                  $data [] = $this->customPhp($field);
              break;
              case 'calculatorSum':
                  $data [] = $this->calculatorSum($field);
              break;
              case 'recaptcha':
                  $this->recaptcha($field);
              break;
              case 'submit':
                  $this->submited = true;
                  $this->redirect = $field->redirect;
              break;
              case 'backemail':
                  $data [] = $this->backemail($field);
              break;
              case 'cloner':
                  $data [] = $this->cloner($field);
              break;
              case 'qfcalendar':
                  $data [] = $this->qfcalendar($field);
              break;
              case 'stepperbox':
                  $data [] = $this->stepperbox($field);
              break;
              case 'stepperbtns':
                  $data [] = $this->stepperbtns($field);
              break;
              case 'qfincluder':
                  $data [] = $this->qfincluder($field);
              break;
              case 'qftabs':
                  $data [] = $this->qftabs($field);
              break;
              case 'addToCart':
                  $this->iscart = true;
              break;
              case 'input[file]':
              case 'qf_file':
                  $data [] = $this->qffile($field);
              break;
              case 'input[button]':
              case 'input[reset]':
              break;
              default:
                $data [] = $this->getDefault($field);
            }
        }

        return $data;
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

    protected function chekRequired($field)
    {
        if ($this->get('required', $field)) {
            if (! $field->value) {
                if ($err = $this->translate($this->get('label', $field))) {
                } elseif ($err = $this->translate($this->get('placeholder', $field))) {
                } else {
                    $err = $field->teg;
                }

                $this->errormes[] = JText::_('COM_QF_NOT_ALL') . ': '. $err;
            }
        }
    }

    protected function checklist($name, $i)
    {
        if (isset($_POST [$name] [$i])) {
            return $_POST [$name] [$i];
        } else {
            $this->errormes[] = JText::_('QF_FORM_ERROR') . '_' . $name;
        }
    }




    protected function getDefault($field)
    {
        static $i = array();

        $name = 'qf'.str_replace(array('input[',']','qf_'), '', $field->teg);

        $i[$name] = isset($i[$name])? $i[$name] : 0;
        $field->value = strip_tags($this->checklist($name, $i[$name]));

        $this->chekRequired($field);
        $i[$name] ++;

        return $field;
    }

    protected function select($field)
    {
        static $i = 0;

        $field->value = (int) $this->checklist('qfselect', $i);
        $this->chekRequired($field);

        $option = $field->options[$field->value];
        $field->math = $this->get('math', $option);
        $field->value = $option->label;
        unset($field->options);
        $i ++;

        if ($id = (int) $this->get('related', $option)) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function radio($field)
    {
        static $i = 0;

        $field->value = (int) $this->checklist('qfradio', $i);
        $this->chekRequired($field);

        $option = $field->options[$field->value];
        $field->math = $this->get('math', $option);
        $field->value = $option->label;
        unset($field->options);
        $i ++;

        if ($id = (int) $this->get('related', $option)) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function checkbox($field)
    {
        static $i = 0;

        $field->value = (int) $this->checklist('qfcheckbox', $i);
        $this->chekRequired($field);

        if (!$field->value) {
            $field->math = '';

            if ($this->get('hide', $field)== 2) {
                $field->hide = 1;
            }
        }

        $i ++;

        if ($field->value) {
            if ($id = (int) $this->get('related', $field)) {
                $field->data = $this->getChildren($id);
            }
            $field->value = 'QF_YES';
        } else {
            $field->value = 'QF_NO';
        }

        return $field;
    }

    protected function textarea($field)
    {
        static $i = 0;

        $val = $this->checklist('qftextarea', $i);
        $val = nl2br(strip_tags($val, '<a></a>'));
        $field->value = $val;
        $this->chekRequired($field);
        $i ++;

        return $field;
    }

    protected function customHtml($field)
    {
        if (!$this->get('qfshowl', $field)) {
            $field->hide = 1;
        }

        return $field;
    }

    protected function customPhp($field)
    {
        $field->value = '';
        if (!$this->get('customphp2', $field)) {
            return $field;
        }

        $tmpfname = tempnam(sys_get_temp_dir(), "qf");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $field->customphp2, strlen($field->customphp2));
        fclose($handle);
        if (is_file($tmpfname)) {
            ob_start();
            include $tmpfname;
            $field->value =  ob_get_clean();
        }
        unlink($tmpfname);
        return $field;
    }

    protected function calculatorSum($field)
    {
        $this->calculated = true;
        $field->hide = 1;
        $field->unit = $this->get('unit', $field);
        $field->pos = $this->get('pos', $field);
        $field->fixed = $this->get('fixed', $field, 0);
        $field->format = $this->get('format', $field, 0);
        return $field;
    }

    protected function recaptcha($field)
    {
        if ($this->ajaxform && $this->app->input->get('mod')!='qfajax') {
            return;
        }

        $params = JComponentHelper::getParams('com_qf3');

        if ($this->user->get('guest') || ! $params->get('recaptcha_show')) {
            if (!isset($_POST["g-recaptcha-response"])) {
                $this->errormes[] = JText::_('RECAPTCHA_ERROR');
                return;
            }
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $params->get('serverkey'),
                'response' => $_POST["g-recaptcha-response"]
              ];
            $options = [
                'http' => [
                  'method' => 'POST',
                  'content' => http_build_query($data)
                ]
              ];
            $context  = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $res=json_decode($verify);
            if (!$res->success) {
                $this->errormes[] = JText::_('RECAPTCHA_ERROR');
            }
        }
    }

    protected function backemail($field)
    {
        if ($this->get('reg', $field)) {
            $field->back = $this->user->get('email');
        } else {
            $field->back = $this->checklist('qfuseremail', 0);
        }

        $field->hide = 1;

        if ($this->get('qfshowf', $field)) {
            $field->value = $this->app->input->get('qfbackemail');

            if ($field->value && $field->back) {
                $this->back = $field->back;
            }

            $this->chekRequired($field);
        } elseif ($field->back) {
            $this->back = $field->back;
        }

        return $field;
    }

    protected function cloner($field)
    {
        static $i = 0;

        $val = (int) $this->checklist('qfcloner', $i);

        if (!$val) {
            $this->errormes[] = JText::_('QF_FORM_ERROR') . '_qfcloner_empty';
        }

        $max = (int) $this->get('max', $field);
        if ($max && $val > $max) {
            $this->errormes[] = JText::_('QF_FORM_ERROR') . '_qfcloner_max';
        }

        $field->value = $val;
        $field->orient = $this->get('orient', $field);
        $field->data = array();
        $i ++;

        for ($n = 0; $n < $val; $n ++) {
            $field->data [] = $this->getChildren($field->related);
        }

        return $field;
    }

    protected function qfcalendar($field)
    {
        static $i = 0;

        if ($this->get('double', $field)) {
            $val1 = $this->checklist('qfcalendar', $i);
            $i ++;
            $val2 = $this->checklist('qfcalendar', $i);
            $val = $val1 . ' — ' . $val2;
            $math = $this->get('math', $field);
            if (strpos($math, 'v') !== false) {
                $format = $this->get('format', $field, 'd-m-Y');
                $date1 = DateTime::createFromFormat($format, $val1);
                $date2 = DateTime::createFromFormat($format, $val2);
                $diff = (strtotime($date2->format('Y-m-d H:i')) - strtotime($date1->format('Y-m-d H:i')))/3600/24;
                if ($diff < 0) {
                    $diff=0;
                } else {
                    $diff=ceil($diff);
                }
                $field->math = str_replace('v', $diff, $field->math);
            }
        } else {
            $val = $this->checklist('qfcalendar', $i);
            if (strpos($math, 'v') !== false) {
                $field->math = str_replace('v', '0', $field->math);
            }
        }

        $field->value = strip_tags($val);

        $this->chekRequired($field);
        $i ++;

        return $field;
    }

    protected function stepperbox($field)
    {
        static $i = 0;
        $this->stepperdata[$i] = array();
        $field->hide = 3;
        if ($id = $this->get('related', $field)) {
            $data = $this->getChildren($id);
            $this->recursively($data, $i);
            $field->data = array_merge($data, $this->stepperdata[$i]);
        }
        $i ++;

        return $field;
    }

    protected function stepperbtns($field)
    {
        static $i = 0;

        $val = (int) $this->checklist('qfstepper', $i);
        $i ++;
        $field->hide = 3;

        if ($val && $id = $this->get('related', $field)) {
            $field->step = $id;
        }

        return $field;
    }

    protected function recursively($data, $i)
    {
        foreach ($data as $field) {
            if ($field->teg == 'stepperbtns') {
                if (isset($field->step) && ! empty($field->step)) {
                    $dat = $this->getChildren($field->step);
                    $this->stepperdata[$i] = array_merge($this->stepperdata[$i], $dat);
                    $this->recursively($dat, $i);
                }
            } else {
                if (isset($field->data) && ! empty($field->data)) {
                    $this->recursively($field->data, $i);
                }
            }
        }
    }

    protected function qfincluder($field)
    {
        $field->hide = 3;
        if ($id = $this->get('related', $field)) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function qftabs($field)
    {
        foreach ($field->options as $option) {
            if ($id = (int) $this->get('related', $option)) {
                $field->data [] = $this->getChildren($id);
            } else {
                $field->data [] = array();
            }
        }

        return $field;
    }

    protected function qffile($field)
    {
        static $i = 0;

        if (!isset($_FILES ['inpfile']['name'][$i])) {
            $this->errormes[] = JText::_('QF_FORM_ERROR') . '_' . 'input[file]';
            $i ++;
            return;
        }
        if (!isset($field->filetoemail)) {
            $field->filetoemail = 1;
        }
        if (!isset($field->extens)) {
            $field->extens = "jpg,gif,png";
        }

        $field->filelist = array();
        $extens = explode(',', strtolower(str_replace(' ', '', $this->get('extens', $field))));
        $extens = array_diff($extens, array(''));
        $html = '';

        foreach ($_FILES ['inpfile']['name'][$i] as $k => $v) {
            if ($v) {
                $err = $_FILES ['inpfile']['error'][$i][$k];
                if ($err) {
                    if ($err = 1) {
                        $this->errormes[] = JText::_('QF_ERR_DOWNLOAD_1') . ': '. $v;
                    } else {
                        $this->errormes[] = $err . ': ' .JText::_('QF_ERR_DOWNLOAD') . ': '. $v;
                    }
                }
                if ($_FILES ['inpfile']['tmp_name'][$i][$k] == 'none' || !is_uploaded_file($_FILES ['inpfile']['tmp_name'][$i][$k])) {
                    $this->errormes[] = $err . ': ' .JText::_('QF_ERR_DOWNLOAD') . ': '. $v;
                }
                if (mb_substr(trim($v), 0, 1, "UTF-8") == '.') {
                    $this->errormes[] = JText::_('QF_ERR_FILE_NAME') . ': '. $v;
                }
                if ($extens) {
                    if (!in_array(strtolower(pathinfo($v, PATHINFO_EXTENSION)), $extens)) {
                        $this->errormes[] = JText::_('QF_ERR_FILE_EXT') . ': '. $v;
                    }
                }
                $arr = array(
                    'name'=>$v,
                    'tmp_name'=>$_FILES ['inpfile']['tmp_name'][$i][$k],
                    'type'=>$_FILES ['inpfile']['type'][$i][$k],
                    'size'=>$_FILES ['inpfile']['size'][$i][$k],
                    'error'=>$_FILES ['inpfile']['error'][$i][$k]
                );
                $field->filelist[] = $arr;

                if ($this->get('filetoemail', $field)) {
                    $this->fileListToEmail[] = $arr;
                }

                if ($this->qf_params->get('filesmod') && $this->get('filetoserver', $field)) {
                    $this->fileListToServer[] = $arr;
                    $html .= '<a href="'.JUri::root().'components/com_qf3/assets/attachment/COM_QF_TEMP_FOLDER_NAME/'.$v.'">'.$v.'</a><br/>';
                } else {
                    $html .= $v . '<br/>';
                }
            }
        }

        $field->value = $html;

        $this->chekRequired($field);
        $i ++;
        return $field;
    }

    public function uploadfiles($html)
    {
        if (!empty($this->fileListToServer)) {
            $foldername = (int) time();
            if ($foldername < 1601572894) {
                $this->errormes[] =  JText::_('QF_ERR_DOWNLOAD');
                return false;
            }
            $path = dirname(__DIR__).'/assets/attachment/'.$foldername.'/';

            $blacklist = array('.php', '.cgi', '.pl', '.fcgi', '.scgi', '.sql', '.phtml', '.asp', '.js', '.py', '.exe', '.htm', '.htaccess', '.htpasswd', '.ini', '.sh', '.log');

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            foreach ($this->fileListToServer as $file) {
                if (str_replace($blacklist, '', strtolower($file['name'])) != strtolower($file['name'])) {
                    $this->errormes[] = JText::_('QF_ERR_FILE_EXT') . ': '. $file['name'];
                    return false;
                }
                if (!move_uploaded_file($file ['tmp_name'], $path . $file ['name'])) {
                    $this->errormes[] =  JText::_('QF_ERR_DOWNLOAD') . ' ' . $file ['name'];
                    return false;
                }
            }

            return str_replace('COM_QF_TEMP_FOLDER_NAME', $foldername, $html);
        }
        return $html;
    }



    public function writeStat($project, $html)
    {
        if ($project->params) {
            if (! $project->params->history) {
                return true;
            }
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $columns = array(
                'st_formid',
                'st_date',
                'st_form',
                'st_title',
                'st_ip',
                'params',
                'st_user',
                'st_status'
        );
        $values = array(
                $project->id,
                $db->quote(gmdate('Y-m-d H:i:s')),
                $db->quote($html),
                $db->quote($this->translate($project->title)),
                $db->quote(@$_SERVER['HTTP_CLIENT_IP'] ?: @$_SERVER['HTTP_X_FORWARDED_FOR'] ?: @$_SERVER['REMOTE_ADDR']),
                '""',
                $this->user->get('id'),
                0
        );
        $query->insert($db->quoteName('#__qf3_ps'))->columns($db->quoteName($columns))->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();
        $res1 = $db->insertid();

        $db->setQuery("UPDATE `#__qf3_projects` SET hits = ( hits + 1 ) WHERE id = " . ( int ) $project->id);
        $res2 = $db->execute();

        if ($res1 && $res2) {
            return $res1;
        }
        return false;
    }

    public function sendMail($project, $html, $statid=false)
    {
        $mailfrom = $this->app->get('mailfrom');
        $fromname = $this->app->get('fromname');
        $sitename = $this->app->get('sitename');

        $mail = JFactory::getMailer();

        if ($project->emailparams->toemail) {
            $arr = explode(',', $project->emailparams->toemail);
            foreach ($arr as $ar) {
                $mail->addRecipient(trim($ar));
            }
        } else {
            $mail->addRecipient($mailfrom);
        }

        $mail->setSender(array($mailfrom, $fromname));

        $replyto = isset($_POST ['qfuseremail'] [0])?$_POST ['qfuseremail'] [0]:'';
        $replytoname = isset($_POST ['qfusername'] [0])?$_POST ['qfusername'] [0]:'';
        if ($replyto) {
            if ($replytoname) {
                $mail->addReplyTo(JStringPunycode::emailToPunycode($replyto), $replytoname);
            } else {
                $mail->addReplyTo(JStringPunycode::emailToPunycode($replyto));
            }
        } else {
            $mail->addReplyTo($mailfrom, $fromname);
        }

        if (is_numeric($statid)) {
            $pre = 'id: '.$statid.'. ';
        } else {
            $pre = 'tick: '.time().'. ';
        }

        if ($project->emailparams->subject) {
            $mail->setSubject($pre.$this->translate($project->emailparams->subject));
        } else {
            $mail->setSubject($pre.$this->translate($project->title));
        }

        $html = $this->modifyHtml($project, $html, $statid);

        $mail->setBody($html);
        if ($project->emailparams->tmpl != 'simple') {
            $mail->isHTML(true);
        }

        foreach ($this->fileListToEmail as $file) {
            $mail->addAttachment($file ['tmp_name'], $file ['name']);
        }

        if ($mail->Send()) {
            if ($this->back) {
                return $this->sendMailBack($project, $html, $statid);
            }
            return true;
        }
        return false;
    }

    public function sendMailBack($project, $html, $statid)
    {
        $mailfrom = $this->app->get('mailfrom');
        $fromname = $this->app->get('fromname');
        $sitename = $this->app->get('sitename');

        $mail = JFactory::getMailer();

        $mail->addRecipient(JStringPunycode::emailToPunycode($this->back));
        $mail->setSender(array($mailfrom, $fromname));
        $mail->addReplyTo($mailfrom, $fromname);

        if ($project->emailparams->subject) {
            $mail->setSubject($this->translate($project->emailparams->subject));
        } else {
            $mail->setSubject($this->translate($project->title));
        }

        $html = $this->modifyHtml($project, $html, $statid);

        $mail->setBody($html);
        if ($project->emailparams->tmpl != 'simple') {
            $mail->isHTML(true);
        }

        foreach ($this->fileListToEmail as $file) {
            $mail->addAttachment($file ['tmp_name'], $file ['name']);
        }

        return $mail->Send();
    }

    public function modifyHtml($project, $html, $statid)
    {
        if (isset($this->modify)) {
            return $this->modify;
        }

        if ($project->emailparams->start_text) {
            $html = $project->emailparams->start_text . $html;
        }

        if ($project->emailparams->final_text) {
            $html = $html . $project->emailparams->final_text;
        }

        if ($qfusername = strip_tags($this->checklist('qfusername', 0))) {
            $html = str_replace('{replacerName}', $qfusername, $html);
        } else {
            $html = str_replace('{replacerName}', JText::_('QF_GUEST'), $html);
        }
        $html = str_replace('{replacerId}', $statid, $html);
        $this->modify = str_replace('{replacerDate}', date("m.d.Y"), $html);

        return $this->modify;
    }
}
