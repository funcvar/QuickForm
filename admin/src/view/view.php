<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;

class baseView extends qf_admin
{
    protected $tpl;
    protected $model;
    protected $form;
    protected $pagination;

    public function __construct($tpl)
    {
        $this->tpl = $tpl;
    }

    public function display()
    {
        $this->addScript('js', 'list.js');
        $this->addScript('css', 'list.css');
        \JHtml::_('stylesheet', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css');

        $ses = qf::ses()->get('quickform', []);
        $messages = $this->get('messages', $ses, []);

        if($messages) {
            $ses['messages'] = false;
            qf::ses()->set('quickform', $ses);

            $html = '<div class="qfmessbox"><div class="qfmessboxclose" onclick="this.parentNode.parentNode.removeChild(this.parentNode)">&#x2715</div>';
            if(isset($messages['err']) && $messages['err']) {
                foreach($messages['err'] as $err) {
                    $html .=  '<div class="qferrormess"><h3>QF_ERROR</h3>'.$err.'</div>';
                }
            }
            if(isset($messages['mes']) && $messages['mes']) {
                foreach($messages['mes'] as $mes) {
                    $html .=  '<div class="qfmessage"><h3>QF_MESSAGE</h3>'.$mes.'</div>';
                }
            }
            $html .= '</div>';
            echo Text::translate($html);
        }

        $tmplpath = QF3_ADMIN_DIR . 'tmpl/' . $this->tpl . '.php';

        if(qf::cmsVersion() == 'j3' && in_array($this->tpl, array('projects','forms','historys','attachment'))) {
            require_once QF3_ADMIN_DIR . 'helpers/qf3.php';
            Qf3Helper::addSubmenu($this->tpl);
            echo '<div class="qfwrap"><div id="j-sidebar-container" class="span2">'.\JHtmlSidebar::render().'</div><div id="j-main-container" class="span10">';
            if (file_exists($tmplpath)) {
                require_once $tmplpath;
            }
            echo '</div></div>';
        }
        else {
            if (file_exists($tmplpath)) {
                require_once $tmplpath;
            }
        }

    }

    public function getItems()
    {
        if (! isset($this->items)) {
            $model = $this->getModel();

            if ($model) {
                $this->items = $model->getItems();
            } else {
                $this->items = false;
            }
        }

        return $this->items;
    }

    public function getForm()
    {
        require_once QF3_ADMIN_DIR . 'forms/form.php';
        return new Form($this->tpl, $this->getItems());
    }


    public function getPagination()
    {
        $ses = qf::ses()->get('quickform', []);
        $model = $this->getModel();
        $itemscount = $model->itemscount;
        $filterlist = $this->get('filterlist', $ses);
        $limit = $this->get($this->tpl.'.limit', $filterlist);
        $count_show_pages = (int) $limit ? (int) $limit : 12;
        $count_pages = ceil($itemscount/$count_show_pages);
        $active = isset($_GET['start']) ? (int) $_GET['start'] : 1;

        $tpl = $this->tpl;
        if($this->tpl == 'forms') {
            $projectid = (int) $this->get('projectid', $_GET);
            $tpl = 'projects&task=forms&projectid='.$projectid;
        }
        $url = 'index.php?option=com_qf3&view='.$tpl;
        $html = '';

        if ($count_pages > 1) {
            $left = $active - 1;
            if ($left < floor($count_show_pages / 2)) {
                $start = 1;
            } else {
                $start = $active - floor($count_show_pages / 2);
            }
            $end = $start + $count_show_pages - 1;
            if ($end > $count_pages) {
                $start -= ($end - $count_pages);
                $end = $count_pages;
                if ($start < 1) {
                    $start = 1;
                }
            }

            $html .= '<div id="pagination">';

            if ($active != 1) {
                $html .= '<a href="'.$url.'" title="First page">&lt;&lt;&lt;</a><a href="';
                if ($active == 2) {
                    $html .= $url;
                } else {
                    $html .= $url.'&start='.($active - 1);
                }
                $html .= '" title="Previous page">&lt;</a>';
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($i == $active) {
                    $html .= '<span>'.$i.'</span>';
                } else {
                    $html .= '<a href="';
                    if ($i == 1) {
                        $html .= $url;
                    } else {
                        $html .= $url.'&start='.$i;
                    }
                    $html .= '">'.$i.'</a>';
                }
            }

            if ($active != $count_pages) {
                $html .= '<a href="'.$url.'&start='.($active + 1).'" title="Next page">&gt;</a><a href="'.$url.'&start='.$count_pages.'" title="Last page">&gt;&gt;&gt;</a>';
            }

            $html .= '<span class="smol"> ~'. $itemscount .'~</span></div>';
        }
        return $html;
    }




    public function getModel()
    {
        if (! isset($this->model)) {
            $modelpath = QF3_ADMIN_DIR . 'src/model/' . $this->tpl . '.php';

            if (is_file($modelpath)) {
                require_once(QF3_ADMIN_DIR . 'src/model/model.php');
                require_once($modelpath);

                $modelname = 'QuickForm\\' . $this->tpl . 'Model';
                $this->model = new $modelname();
            } else {
                $this->model = false;
                $this->errors[] = 'model not found';
            }
        }
        return $this->model;
    }



    protected function toolbarBtn($task, $text, $class = '')
    {
        $cl = explode('.', $task);
        if($task == 'form.addfield') {
            return '<div class="qf3_toolbar_cell"><a href="javascript:void(0);" class="fa '.$cl[1] . $class.'">' . Text::_($text) . '</a></div>';
        }
        elseif($cl[1] == 'help') {
            return '<div class="qf3_toolbar_cell_right"><a href="javascript:QFlist.help(\''.$task.'\');" class="fa '.$cl[1] . $class.'">' . Text::_($text) . '</a></div>';
        }
        elseif($cl[1] == 'import') {
            return '<div class="qf3_toolbar_cell"><a href="javascript:QFlist.help(\''.$task.'\');" class="fa '.$cl[1] . $class.'">' . Text::_($text) . '</a></div>';
        }
        else {
            return '<div class="qf3_toolbar_cell"><a href="javascript:QFlist.qflistedit(\''.$task.'\');" class="fa '.$cl[1] . $class.'">' . Text::_($text) . '</a></div>';
        }
    }

    protected function filter($task, $options)
    {
        $ses = qf::ses()->get('quickform', []);

        $cl = explode('.', $task);
        $html = '<div class="qf3_filter">';
        $html .= '<select name="filter['.$cl[1].']" class="js_sort_list" data-order="'.$task.'">';

        $ses = isset($ses['filterlist'][$task]) ? $ses['filterlist'][$task] : '';

        foreach ($options as $k => $option) {
            $selected = '';
            if ((string)$ses === (string)$k) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="'.$k.'"'.$selected.'>'.Text::_($option).'</option>';
        }

        $html .= '</select>';
        $html .= '</div>';
        return $html;
    }

    protected function filtersearch($task)
    {
        $ses = qf::ses()->get('quickform', []);

        $filter = isset($ses['filterlist'][$task]) ? $ses['filterlist'][$task] : '';
        $html = '<div class="qf3_filter">';
        $html .= '<input type="text" name="filter[search]" class="search" value="'.$filter.'" placeholder="'.Text::_('Search').'" data-order="'.$task.'">';
        $html .= '<button class="fa searchbtn" onclick="QFlist.filtersearch()"></button>';
        $html .= '</div>';
        return $html;
    }

    protected function filterdir($task, $text)
    {
        $ses = qf::ses()->get('quickform', []);

        $filter = $this->get('filterdir', $ses, []);
        $dir = $this->get('dir', $filter) == 'asc' ? 'asc' : 'desc';
        $class = ($this->get('order', $filter, 'id') == $task) ? '-'.$dir : '';

        return '<a href="" onclick="return false;" class="js_sort_dir" data-order="'.$this->tpl.'.'.$task.'"><span>'.Text::_($text).'</span><span class="fa fa-sort'.$class.'" aria-hidden="true"></span></a>';
    }

    public function publishbtn($item)
    {
        $cl = $item->published ? 'fa fa-check' : 'fa fa-times';
        return '<a href="index.php?option=com_qf3&task=projects.publish&id='.$item->id.'" class="'.$cl.'"></a>';
    }


    public function settitle($text)
	{
		\JToolbarHelper::title(Text::translate($text), 'envelope inbox');
	}
}
