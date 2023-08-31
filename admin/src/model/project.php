<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class projectModel extends baseModel
{
    public $closetofields;

    public function __construct()
    {
        $this->closelink = 'projects';
        $this->savelink = 'projects&task=project.edit';
        $this->savecreatelink = 'projects&task=project.edit&id=0';
        $this->closetofields = 'projects&task=forms&projectid=' . (int) $this->get('id', $_GET);
    }

    public function getItems()
    {
        $task = qf::gettask();
        $id = (int) $this->get('id', $_GET);
        $ses = qf::ses()->get('quickform', []);

        if ($task === 'project.add') {
            return;
        }

        if ($task === 'project.edit' && $id === 0) {
            $data = (object) $this->get('project.savecreate', $ses);
            if (! isset($data->title)) {
                return;
            }
            $data->id = 0;
            $data->title .= ' copy';
        } else {
            $db = \JFactory::getDbo();
            $db->setQuery("SELECT * FROM #__qf3_projects WHERE id = " .(int) $id);
            $data = $db->loadObject();
            $ses['project.savecreate'] = false;
            qf::ses()->set('quickform', $ses);
        }


        $params = json_decode($data->params);
        if ($params) {
            foreach ($params as $name => $value) {
                $dname = 'p]['.$name;
                $data->$dname = $value;
            }
        }
        unset($data->params);
        return $data;
    }

    public function save()
    {
        $db = \JFactory::getDbo();

        $data = filter_input(INPUT_POST, 'qffield', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY);

        if (! $data['title']) {
            $this->errors[] = 'The title is not filled in.';
            return (int) $data['id'];
        }

        if ($data['csschoose'] == 'n') {
            if (! $res = $this->createCSSfile($data)) {
                return false;
            }
            $data['p']['cssform'] = $res;
        }

        $datap = json_encode($data['p'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $inputData = array(
            'title' 	=> '\''.addslashes(strip_tags($data['title'])).'\'',
            'id' 	=> (int) $data['id'],
            'published'    => (int) $data['published'],
            'access'   => (int) $data['access'],
            'language'   => '\''.addslashes($data['language']).'\'',
            'hits'   => (int) $data['hits'],
            'params'   => '\''.addslashes($datap).'\'',
         );

        if ($_REQUEST['task'] === 'project.savecreate') {
            $data['params'] = $datap;
            $ses = qf::ses()->get('quickform', []);
            $ses['project.savecreate'] = $data;
            qf::ses()->set('quickform', $ses);
        }


        if ($inputData['id']) {
            foreach ($inputData as $key => $value) {
                $updates[] = $key .' = '. $value;
            }
            $db->setQuery("UPDATE #__qf3_projects SET ".implode(', ', $updates). " WHERE id=".(int) $inputData['id']);
            $db->execute();
            return $inputData['id'];
        } else {
            $db->setQuery("INSERT INTO #__qf3_projects (".implode(',', array_keys($inputData)).") VALUES (".implode(',', array_values($inputData)).")");
            $db->execute();
            return $db->insertid();
        }
    }

    protected function createCSSfile($data)
    {
        if ($name = $data['createcssfile']) {
            $pats = explode('.', $name);
            preg_match('/[a-z0-9_]+/i', $pats[0], $matches);

            if ($matches[0] != $pats[0]) {
                $this->errors[] = 'File not created. Invalid file name: '.htmlspecialchars($pats[0]).'.css';
                return false;
            }

            $name = QF3_PLUGIN_DIR . 'assets/css/'.$matches[0].'.css';

            if (file_exists($name)) {
                $this->errors[] = $name.'<br>This file already exists.';
                return false;
            }

            if (isset($data['copycssfile'])) {
                if (! file_exists(QF3_PLUGIN_DIR . 'assets/css/default.css')) {
                    $this->errors[] = 'default.css file is missing.';
                    return false;
                }
                $def = file_get_contents(QF3_PLUGIN_DIR . 'assets/css/default.css');
                file_put_contents($name, str_replace('default', $matches[0], $def));
            } else {
                file_put_contents($name, "/**
                @package
                *Joomla & QuickForm
                */".PHP_EOL);
            }

            return ($matches[0].'.css');
        }
        $this->errors[] = 'File not created. Empty file name';
        return false;
    }
}
