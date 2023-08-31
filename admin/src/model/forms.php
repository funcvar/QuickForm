<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class formsModel extends baseModel
{
    public $itemscount;
    public $projectid;
    public $total;

    public function __construct()
    {
        $this->projectid = (int) $this->get('projectid', $_REQUEST);
        $this->closelink = 'projects&task=forms&projectid=' . $this->projectid;
    }

    public function getItems()
    {
        $db = \JFactory::getDbo();

        $ses = qf::ses()->get('quickform', []);
        $filterdir = $this->get('filterdir', $ses, []);

        $order = $this->get('order', $filterdir, 'id');
        if (! in_array($order, array('def', 'title'))) {
            $order = 'id';
        }

        $dir = $this->get('dir', $filterdir) == 'asc' ? 'asc' : 'desc';

        if (! $filterdir) {
            $ses['filterdir'] = array('order'=>$order, 'dir'=>$dir);
            qf::ses()->set('quickform', $ses);
        }

        $filterlist = $this->get('filterlist', $ses, []);
        $data = array();

        foreach ($filterlist as $k=>$v) {
            if ($v && $k == 'forms.search') {
                $v = '\'%'.addslashes(addcslashes(htmlspecialchars_decode(trim($v), ENT_QUOTES), '_%\\' )).'%\'';
                $db->setQuery( 'SELECT * FROM #__qf3_forms WHERE (title LIKE ' . $v . ' OR fields LIKE ' . $v . ') AND  projectid = '. (int) $this->projectid .' ORDER BY ' .$order. ' ' .$dir );
                $data = $db->loadObjectList();
            }
        }

        if (! $data) {
          $db->setQuery( 'SELECT * FROM #__qf3_forms WHERE projectid='. (int) $this->projectid. ' ORDER BY ' .$order. ' ' .$dir );
          $data = $db->loadObjectList();
        }

        $arr = array();
        foreach ($data as $row) {
          $arr[$row->id]['data'] = ['id' => $row->id, 'title' => $row->title, 'def' => $row->def];
        }

        foreach ($data as $row) {
          $fields = json_decode($row->fields);
          foreach ($fields as $field) {
            if ($rel = $this->get('related', $field)) {
              if (isset($arr[$rel])) {
                $arr[$row->id]['childs'][] = $rel;
                $arr[$rel]['parents'][] = $row->id;
        }
            }
            elseif ($this->get('options', $field)) {
              foreach ($field->options as $option) {
                if ($rel = $this->get('related', $option)) {
                  if (isset($arr[$rel])) {
                    $arr[$row->id]['childs'][] = $rel;
                    $arr[$rel]['parents'][] = $row->id;
                  }
                }
              }
            }
          }
        }

        return $this->treeHtml($this->getTree($arr));
    }


    public function treeHtml($tree, $i = -1) {
      static $repeat = [];
      $i++; $d = '';
      for($ii=$i; $ii>0; $ii--) $d .= ' — ';
      $html = '';
      foreach ($tree as $row) {
        $item = (object)$row['data'];
        if (! isset($repeat[$item->id])) {
          $html .= '<tr>';
          $html .= '<td><input autocomplete="off" type="checkbox" name="cid[]" value="'.$item->id.'"></td>';
          $html .= '<td>'.$this->defbtn($item).'</td>';
          $html .= '<td class="qftitle"><a href="index.php?option=com_qf3&view=projects&task=form.edit&id='.$item->id.'">'.$d.$item->title.'</a></td>';
          $html .= '<td>'.$item->id.'</td>';
          $html .= '</tr>';
          $repeat[$item->id] = $i;
        }
        if (isset($row['tree']) && $row['tree'])  $html .= $this->treeHtml($row['tree'], $i);
      }
      return $html;
    }

    public function defbtn($item)
    {
        if($item->def) {
            return '<span class="fa fa-star defgreen"></span>';
    }
        return '<a href="index.php?option=com_qf3&view=projects&task=forms.changedef&id='.$item->id.'" class="fa fa-star defgray"></a>';
    }


    public function getTree($arr) {
      $fl = 0;
      foreach ($arr as $a=>$ar) {
        if (! isset($ar['childs']) || ! $ar['childs']) {
          unset($ar['childs']);
          if (isset($ar['parents']) && $ar['parents']) {
            foreach ($ar['parents'] as $v) {
              $arr[$v]['tree'][] = $ar;
              if (($key = array_search($a, $arr[$v]['childs'])) !== false) {
                unset($arr[$v]['childs'][$key]);
                $fl = 1;
              }
            }
            unset($arr[$a]);
          }
        }
      }

      if ($fl) return $this->getTree($arr);
      else return $arr;
    }


    public function getProjectTitle()
    {
        $db = \JFactory::getDbo();

        $db->setQuery( 'SELECT title FROM #__qf3_projects WHERE id = '.(int) $this->projectid );
        return $db->loadResult();
    }


    public function changeDef()
    {
        $db = \JFactory::getDbo();

        $id = (int) $this->get('id', $_GET);
        if ($id) {
            $db->setQuery( 'SELECT projectid FROM #__qf3_forms WHERE id = '.$id );
            $projectid = $db->loadResult();

            $db->setQuery(
                'UPDATE #__qf3_forms SET def = 1 - def WHERE (def = 1 AND projectid = '.(int) $projectid.') OR id = '.$id
            );
            $db->execute();

            return $projectid;
        }
    }
}
