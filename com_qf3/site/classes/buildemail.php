<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) plasma-web.ru
        * @license    GNU/GPL
        */

defined('_JEXEC') or die();

require_once(JPATH_COMPONENT."/classes/calculator.php");
require_once(JPATH_COMPONENT."/classes/email.php");

class qfFilds
{
    public $submited = false;
    public $iscart = false;
    public $errormes = array();
    public $project = false;
    public $redirect = false;
    protected $child = array();
    public $back = false;

    public function __construct()
    {
        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDBO();
        $this->user = JFactory::getUser();
        $this->ajaxform = $this->app->input->get('task')=='ajax';
    }

    public function getResultHtml($project)
    {
        $data = $this->getData($project->id);
        $calculator = qfCalculator::getCalculator($project, $data);
        $html = qfEmail::getEmailHtml($project, $data, $calculator);
        return $html;
    }

    public function sumCustomAjax()
    {
      $strarr = array();
      $id = $this->app->input->get('id', 0, 'int');
      $project = $this->getProjectById($id);
      if(!$project) return '';
      $data = $this->getData($project->id);
      $sumarr = qfCalculator::getCalculator($project, $data);
      foreach($sumarr as $arr){
        $strarr[] = $arr[1][4] . ':' . $arr[0];
      }
      return implode(';', $strarr);
    }

    public function getErrormes()
    {
        $err = qfCalculator::qfErrormes();
        return array_merge($this->errormes, $err);
    }

    public function mlangLabel($val)
    {
        if (strpos($val, 'QF_')===0) {
            return JText::_($val);
        }
        return $val;
    }

    public function qfcheckToken()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $token = explode('/', str_replace(array('w', '.', '-', '|'), '', JURI::current()));
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
        $this->db->setQuery('SELECT * FROM #__qf3_projects' . ' WHERE published=1' . ' AND (language=' . $this->db->quote($lang->getTag()) . ' OR language=' . $this->db->quote('*') . ')' . ' AND access IN (' . $groups . ')' . ' AND id = ' . ( int ) $id);
        $this->project = $this->db->loadObject();

        if (empty($this->project)) {
            return false;
        }

        $this->project->params = json_decode($this->project->params);
        $this->project->formparams = json_decode($this->project->formparams);
        $this->project->emailparams = json_decode($this->project->emailparams);
        $this->project->calculatorparams = json_decode($this->project->calculatorparams);

        if (isset($this->project->params->languagelink) && $this->project->params->languagelink) {
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
        if (! isset($this->child [$id])) {
            $this->db->setQuery('SELECT * FROM #__qf3_forms WHERE id = ' . ( int ) $id);
            $this->child [$id] = $this->db->loadObject();
        }

        return $this->getFields($this->child [$id]);
    }

    protected function getFields($form)
    {
        $data = array();
        if (! $form) {
            return $data;
        }
        $fields = json_decode($form->fields);

        foreach ($fields as $field) {
            $field->fildid = $form->id . '.' . $field->fildnum;
            unset($field->fildnum);

            switch ($field->teg) {
              case 'input[text]':
                $data [] = $this->getText($field);
              break;
              case 'select':
                $data [] = $this->getSelect($field);
              break;
              case 'input[radio]':
                $data [] = $this->getRadio($field);
              break;
              case 'input[checkbox]':
              case 'qf_checkbox':
                $data [] = $this->getCheckbox($field);
              break;
              case 'userName':
                $data [] = $this->getDefault($field, 'qfusername');
              break;
              case 'userEmail':
                $data [] = $this->getDefault($field, 'qfuseremail');
              break;
              case 'userPhone':
                $data [] = $this->getDefault($field, 'qfuserphone');
              break;
              case 'textarea':
                $data [] = $this->getTextarea($field);
              break;
              case 'customHtml':
                  $data [] = $this->getCustomHtml($field);
              break;
              case 'customPhp':
                  $data [] = $this->getCustomPhp($field);
              break;
              case 'calculatorSum':
                  $data [] = $this->getCalculatorSum($field);
              break;
              case 'recaptcha':
                  $this->getRecaptcha($field);
              break;
              case 'submit':
                  $this->submited = true;
                  $this->redirect = $this->get('redirect', $field);
              break;
              case 'backemail':
                  $data [] = $this->getBackemail($field);
              break;
              case 'cloner':
                  $data [] = $this->getCloner($field);
              break;
              case 'qfincluder':
                  $data [] = $this->getQfincluder($field);
              break;
              case 'qftabs':
                  $data [] = $this->getTabs($field);
              break;
              case 'addToCart':
                  $this->iscart = true;
              break;
              case 'input[file]':
                  $data [] = $this->getFile($field);
              break;
              case 'input[hidden]':
                $data [] = $this->getDefault($field, 'qfhidden');
              break;
              case 'input[color]':
                $data [] = $this->getDefault($field, 'qfcolor');
              break;
              case 'input[date]':
                $data [] = $this->getDefault($field, 'qfdate');
              break;
              case 'input[email]':
                $data [] = $this->getDefault($field, 'qfemail');
              break;
              case 'qf_number':
              case 'input[number]':
                $data [] = $this->getDefault($field, 'qfnumber');
              break;
              case 'qf_range':
              case 'input[range]':
                $data [] = $this->getDefault($field, 'qfrange');
              break;
              case 'input[tel]':
                $data [] = $this->getDefault($field, 'qftel');
              break;
              case 'input[url]':
                $data [] = $this->getDefault($field, 'qfurl');
              break;
              case 'input[button]':
              case 'input[reset]':
              break;
              default:
                  $data [] = $field;
            }
        }

        return $data;
    }

    protected function chekRequired($field, $val)
    {
        if ($this->get('required', $field)) {
            if (! $val) {
                if ($field->label) {
                    $err = $this->mlangLabel($field->label);
                } elseif($this->get('placeholder', $field)){
                    $err = $this->mlangLabel($field->placeholder);
                } else {
                    $err = $field->teg;
                }

                $this->errormes[] = JText::_('COM_QF_NOT_ALL') . ': '. $err;
            }
        }
    }

    protected function getVal($name, $i)
    {
        if (isset($_POST [$name] [$i])) {
            return $_POST [$name] [$i];
        } else {
            $this->errormes[] = JText::_('FORM_ERROR') . '_' . $name;
        }
    }

    protected function get($v, $obj, $def = '')
    {
        return (isset($obj->$v) && $obj->$v) ? $obj->$v : $def;
    }


    protected function getText($field)
    {
        static $i = 0;

        $val = $this->getVal('qftext', $i);
        $val = strip_tags($val);

        $field->value = $val;
        $this->chekRequired($field, $val);
        $i ++;

        return $field;
    }

    protected function getSelect($field)
    {
        static $i = 0;

        $val = ( int ) $this->getVal('qfselect', $i);
        $this->chekRequired($field, $val);

        $option = $field->options [$val];

        $field->math = isset($option->math)?$option->math:'';
        $field->value = $option->label;
        unset($field->options);
        $i ++;

        $related = $this->get('related', $option);
        if ($id = ( int ) $related) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function getRadio($field)
    {
        static $i = 0;

        $val = ( int ) $this->getVal('qfradio', $i);
        $this->chekRequired($field, $val);

        $option = $field->options [$val];

        $field->math = isset($option->math)?$option->math:'';
        $field->value = $option->label;
        unset($field->options);
        $i ++;

        $related = $this->get('related', $option);
        if ($id = ( int ) $related) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function getCheckbox($field)
    {
        static $i = 0;

        $val = ( int ) $this->getVal('qfcheckbox', $i);
        $field->value = $val;
        $this->chekRequired($field, $val);


        if (!$val) {
            $field->math = '';

            if ($this->get('hidech', $field)) {
                $field->hide = 1;
            }
        }

        $i ++;

        if ($id = $this->get('related', $field)) {
            if ($field->value) {
                $field->data = $this->getChildren($id);
            }
        }

        if ($field->value) {
            $field->value = 'QF_YES';
        } else {
            $field->value = 'QF_NO';
        }

        return $field;
    }

    protected function getDefault($field, $name)
    {
        static $i = array();
        $i[$name] = isset($i[$name])? $i[$name] : 0;

        $val = $this->getVal($name, $i[$name]);
        $val = strip_tags($val);

        $field->value = $val;
        $this->chekRequired($field, $val);
        $i[$name] ++;

        return $field;
    }

    protected function getTextarea($field)
    {
        static $i = 0;

        $val = $this->getVal('qftextarea', $i);
        $val = nl2br(strip_tags($val, '<a></a>'));
        $field->value = $val;
        $this->chekRequired($field, $val);
        $i ++;

        return $field;
    }

    protected function getFile($field)
    {
        static $i = 0;

        $val = ( int ) $this->getVal('qffile', $i);

        $files = $this->app->input->files->get('inpfile', array(), 'array');
        $field->value = '';
        if (isset($files [$i] ['name'])) {
            $field->value = $files [$i] ['name'];
        }

        $this->chekRequired($field, $field->value);
        $i ++;

        return $field;
    }

    protected function getCloner($field)
    {
        static $i = 0;

        $val = ( int ) $this->getVal('qfcloner', $i);

        if(!$val){
          $this->errormes[] = JText::_('FORM_ERROR') . '_qfcloner_empty';
        }

        $max = $field->max;
        $id = ( int ) $field->related;
        if ($max && $val > $max) {
            $this->errormes[] = JText::_('FORM_ERROR') . '_qfcloner_max';
        }
        $field->value = $val;
        $field->orient = $this->get('orient', $field);
        $field->data = array();
        $i ++;

        for ($n = 0; $n < $val; $n ++) {
            $field->data [] = $this->getChildren($id);
        }

        return $field;
    }

    protected function getTabs($field)
    {
        foreach ($field->options as $option) {
            if ($id = ( int ) $option->related) {
                $field->data [] = $this->getChildren($id);
            } else {
                $field->data [] = array();
            }
        }

        return $field;
    }

    protected function getQfincluder($field)
    {
        if ($id = $this->get('related', $field)) {
            $field->data = $this->getChildren($id);
        }

        return $field;
    }

    protected function getBackemail($field)
    {
        if ($this->get('reg', $field)) {
            $field->back = $this->user->get('email');
        } else {
            $field->back = $this->getVal('qfuseremail', 0);
        }

        $field->value = $this->app->input->get('qfbackemail');

        if ($field->value && $field->back) {
            $this->back = $field->back;
        }

        $field->hide = 1;
        if($this->get('qfshowf', $field)) $this->chekRequired($field, $field->value);
        return $field;
    }

    protected function getRecaptcha($field)
    {
      if ($this->ajaxform && $this->app->input->get('mod')!='qfajax') {
          return;
      }
      if ($this->user->get('guest') || ! $this->get('show', $field)) {
        $code= $this->app->input->get('recaptcha_response_field');
        JPluginHelper::importPlugin('captcha');
        $dispatcher = JDispatcher::getInstance();
        $res = $dispatcher->trigger('onCheckAnswer',$code);
        if(!$res[0]){
          $this->errormes[] = JText::_('RECAPTCHA_ERROR');
        }
      }
    }

    protected function getCalculatorSum($field)
    {
        $field->hide = 1;
        $field->label = $this->mlangLabel($this->get('label', $field));
        $field->unit = $this->mlangLabel($this->get('unit', $field));
        $field->pos = $this->get('pos', $field);
        $field->fixed = $this->get('fixed', $field, 0);
        return $field;
    }

    protected function getCustomHtml($field)
    {
        if (!$this->get('qfshowl', $field)) {
            $field->hide = 1;
        }

        return $field;
    }

    protected function getCustomPhp($field)
    {
        $field->value = '';
        $cod = $this->get('customphp2', $field);
        if(!$cod) return $field;

        $config = JFactory::getConfig();
        $tmpfname = tempnam($config->get('tmp_path'), "qf");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $cod, strlen($cod));
        fclose($handle);
        if (is_file($tmpfname)) {
            ob_start();
            include $tmpfname;
            $field->value =  ob_get_clean();
        }
        unlink($tmpfname);
        return $field;
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
                $db->quote($this->mlangLabel($project->title)),
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
            if($replytoname){
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
            $mail->setSubject($pre.$this->mlangLabel($project->emailparams->subject));
        } else {
            $mail->setSubject($pre.$this->mlangLabel($project->title));
        }

        $html = $this->modifyHtml($project, $html, $statid);

        $mail->setBody($html);
        $mail->isHTML(true);

        $files = $this->app->input->files->get('inpfile', array(), 'array');
        foreach ($files as $file) {
            if (isset($file ['name']) && $file ['tmp_name'] && $file ['name']) {
                $mail->addAttachment($file ['tmp_name'], $file ['name']);
            }
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
            $mail->setSubject($this->mlangLabel($project->emailparams->subject));
        } else {
            $mail->setSubject($this->mlangLabel($project->title));
        }

        $html = $this->modifyHtml($project, $html, $statid);

        $mail->setBody($html);
        $mail->isHTML(true);

        $files = $this->app->input->files->get('inpfile', array(), 'array');
        foreach ($files as $file) {
            if (isset($file ['name']) && $file ['tmp_name'] && $file ['name']) {
                $mail->addAttachment($file ['tmp_name'], $file ['name']);
            }
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

        if ($qfusername = strip_tags($this->getVal('qfusername', 0))) {
            $html = str_replace('{replacerName}', $qfusername, $html);
        }
        else{
          $html = str_replace('{replacerName}', JText::_('QF_GUEST'), $html);
        }
        $html = str_replace('{replacerId}', $statid, $html);
        $this->modify = str_replace('{replacerDate}', date("m.d.Y"), $html);

        return $this->modify;
    }
}
