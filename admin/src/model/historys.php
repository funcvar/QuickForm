<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class historysModel extends baseModel
{
    public $itemscount;

    public function __construct()
    {
        $this->closelink = 'historys';
    }

    public function getItems()
    {
        $db = \JFactory::getDbo();

        $ses = qf::ses()->get('quickform', []);
        $filterdir = $this->get('filterdir', $ses, []);

        $order = $this->get('order', $filterdir, 'id');
        if(! in_array($order, array('st_status', 'st_title', 'st_date', 'st_ip', 'st_user'))) {
            $order = 'id';
        }
        $dir = $this->get('dir', $filterdir) == 'asc' ? 'asc' : 'desc';


        $limit = 12;

        if (! $filterdir) {
          $ses['filterdir'] = array('order'=>$order, 'dir'=>$dir);
          qf::ses()->set('quickform', $ses);
        }

        $filterlist = $this->get('filterlist', $ses, []);
        $where = array();

        foreach($filterlist as $k=>$v) {
            if(($v !== '') && $k == 'historys.st_status') $where[] = 'st_status='. (int) $v;
            elseif($v && $k == 'historys.search') {
                $v = '\'%'.addslashes(addcslashes(htmlspecialchars_decode(trim($v), ENT_QUOTES), '_%\\' )).'%\'';
                $where[] = '(st_title LIKE ' . $v . ' OR st_desk LIKE ' . $v . ' OR st_form LIKE ' . $v . ')';
            }
            elseif($v && $k == 'historys.limit') $limit = (int) $v;
        }

        $active = (int) $this->get('start', $_GET, 1);
        $start = ($active-1)*$limit;

        if($where) {
            $where = ' WHERE ' . implode(' AND ', $where);
        }
        else $where = '';

        $db->setQuery( 'SELECT * FROM #__qf3_ps' .$where. ' ORDER BY ' .$order. ' ' .$dir. ' LIMIT ' .$start. ',' .$limit );
        $data = $db->loadObjectList();

        $db->setQuery( 'SELECT COUNT(*) FROM #__qf3_ps' .$where );
        $this->itemscount = $db->loadResult();

        return $data;
    }

    public function delete($page)
    {
        parent::delete('ps');
    }

    public function statusfields()
    {
        return array(''=>'QF_NOT_SELECTED', 0=>'QF_NEW', 1=>'QF_UNDERWAY', 2=>'QF_ACHIEVED');
    }

    public function csv()
    {
      $db = \JFactory::getDbo();
      $new_id = $this->checkcid();
      if($new_id) {
          header("Content-type: application/octet-stream");
          header("Content-Disposition: attachment; filename=".date("Y-m-d") . ".csv");
          header("Pragma: no-cache");
          header("Expires: 0");

          $db->setQuery( 'SELECT * FROM #__qf3_ps WHERE id IN ('.implode(',', $new_id).')' );
          $items = $db->loadObjectList();
          $arr = array();
          $labels = ['Date', 'Title', 'Form', 'Status', 'User', 'Desk'];
          $options = $this->statusfields();
          array_push($arr, $labels);

          foreach ($items as $item) {
            $status = Text::_($options[$item->st_status]);
            $user = \JFactory::getUser($item->st_user)->get('username');
            $form = strip_tags($item->st_form);
            $ar = [$item->st_date, $item->st_title, $form, $status, $user, $item->st_desk];
            array_push($arr, $ar);
}

          ob_start();
           $df = fopen("php://output", 'w');
           fputs($df, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
           foreach ($arr as $row) {
              fputcsv($df, $row, ';');
           }
           fclose($df);
           echo ob_get_clean();
           die();
      }
    }

}
