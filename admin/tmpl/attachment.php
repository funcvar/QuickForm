<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;

\defined('QF3_VERSION') or die;
?>

<style>
	svg {
		width: 18px;
	    margin: 2px 7px;
	    vertical-align: middle;
	}
</style>

<div class="row">
	<div class="col-lg-12">
<?php

echo '<h3>'.Text::_('QF_DEL_OLD_FILES').'</h3>';
echo '/components/com_qf3/assets/attachment/<br><br>';

class attachment
{
		protected $canDelete;

    public function __construct()
    {
        $this->canDelete = qf::user()->authorise('core.edit', 'com_qf3');
    }

    public function ls_recursive($dir)
    {
        if (is_dir($dir)) {
            $files = scandir($dir, 1);

            foreach ($files as $file) {
                $currentfile = $dir . "/" . $file;

                $count = substr_count($currentfile, '/');
                $minus_count = substr_count($_SERVER['DOCUMENT_ROOT'], '/');
                $count -= ($minus_count + 2);

                $last_dir = "";
                for ($p = 0; $p < $count; $p++) {
                    $last_dir .= "&nbsp;&nbsp;&nbsp;";
                }

                if (is_dir($currentfile)) {
                    if ($file != '.' && $file != '..') {
                        $last_dir .= "<span>".((int)$file?"(".date("d-m-Y", $file).")":"").$this->svgfolder()."<a href=\"javascript:void(0)\" onclick=\"tgldispl(this)\">". $file."</a> (".$this->countFiles($currentfile).") ".$this->delLink($currentfile)."<br>";
                        echo $last_dir;
                        echo "<span style=\"display:none\">";
                        $this->ls_recursive($currentfile);
                        echo "</span></span>";
                    }
                } else {
                    $last_dir .= $this->svgfile()."<a target=\"_blank\" style=\"color:grey\" href=\"".str_replace(QF3_PLUGIN_DIR, QF3_PLUGIN_URL, $currentfile) ."\">". $file . "</a> ".$this->delLink($currentfile)."<br>";
                    echo $last_dir;
                }
            }
        }
    }

    public function delLink($currentfile)
    {
        if (! $this->canDelete) {
            return '';
        }

        $file = str_replace(QF3_PLUGIN_DIR.'assets/attachment/', '', $currentfile);
        $dlink = 'index.php?option=com_qf3&view=attachment&del=';
        return "<em><a style=\"color:red; padding-left:10px;\" href=\"".$dlink.$file."\">delete</a></em>";
    }

    public function countFiles($dir)
    {
        $files = array();
        $directory = opendir($dir);
        while ($item = readdir($directory)) {
            if (($item != ".") && ($item != "..")) {
                $files[] = $item;
            }
        }
        return count($files);
    }


    public function delFiles($file)
    {
        if (! $this->canDelete || ! $file) {
            return;
        }

        $dir = QF3_PLUGIN_DIR.'assets/attachment/'. $file;

        if (is_dir($dir)) {
            return $this->recursiveRemoveDir($dir);
        } elseif (file_exists($dir)) {
            return unlink($dir);
        }
    }

    public function recursiveRemoveDir($dir)
    {
        $includes = new \FilesystemIterator($dir);

        foreach ($includes as $include) {
            if (is_dir($include) && !is_link($include)) {
                $this->recursiveRemoveDir($include);
            } else {
                unlink($include);
            }
        }

        return rmdir($dir);
    }

    public function svgfolder()
    {
        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve">
	<g><path d="M918.8,389.9h-1.1v-84.6c0-35.9-29.2-64.9-64.9-64.9H465.3c-2.9,0-7.8-4-8.6-6.7l-16.1-66.8c-3.7-15.5-13.1-29.7-26.4-40.2c-13.3-10.5-29.3-16.3-45.2-16.3h-294c-35.9,0-64.9,29.2-64.9,64.9v649.3c0,35.8,29.2,64.9,64.9,64.9l782.8-1.1c20.7,0,38.7-14.4,43.4-34.5l16.5-71.5l70.5-305.4C998.6,432.5,964.6,389.9,918.8,389.9z M116.9,181.8h251.6c0.3,0.1,0.9,0.4,1.7,1c0.7,0.6,1,1,1.2,1.2l16,66.4l0.1,0.6l0.1,0.6c8.9,34.3,42.2,60.1,77.5,60.1h345.6c19.7,0,35.6,15.9,35.6,35.6v42.6H191.9c-33.2,0-62,22.9-69.5,55.2L81.3,623.7V217.4C81.3,197.7,97.2,181.8,116.9,181.8z M917.7,465.9l-71.3,308.5l-9.9,42.9H109.6l82.3-356.2h654.5h71.3h1.1L917.7,465.9z"/></g>
	</svg>';
    }

    public function svgfile()
    {
        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve">
	<g><g transform="translate(0.000000,511.000000) scale(0.100000,-0.100000)"><path d="M1403.2,4986.7c-322.4-55.2-598.2-299.1-687.3-613C684.2,4267.6,682,3843.3,686.3,57l6.4-4200l59.4-125.1c114.6-248.2,290.6-403,547.3-481.5c125.1-38.2,254.5-40.3,3699.4-40.3c3519.1,0,3572.1,0,3707.9,42.4c260.9,80.6,485.8,305.5,566.4,566.4c42.4,135.8,42.4,190.9,42.4,3222.1v3084.2L7942.9,3567.6L6572.6,5010l-2526.4-2.1C2652.6,5005.8,1469,4997.3,1403.2,4986.7z M4207.5,4564.6h1945.2l6.4-1088.2l6.4-1088.2l78.5-159.1c97.6-197.3,258.8-356.4,456.1-449.7l144.2-67.9l1024.5-6.4l1022.4-6.4v-2859.4v-2861.5l-59.4-112.4c-46.7-91.2-80.6-125.2-171.8-171.8l-114.5-59.4l-3578.5,4.3l-3580.6,6.4l-91.2,63.6c-48.8,36.1-112.4,106.1-140,157l-48.8,93.3V97.3c0,3956.1,2.1,4142.7,40.3,4223.3c50.9,112.4,171.8,216.4,271.5,233.3c44.5,8.5,91.2,17,101.8,23.3c12.7,4.2,184.6,2.1,381.8-2.1C2101.1,4570.9,3138.4,4564.6,4207.5,4564.6z M7730.8,3207l1005.5-1071.2l-882.4-6.4c-485.8-2.1-912.1,0-948.2,6.4c-89.1,14.9-239.7,148.5-286.4,252.4c-36.1,80.6-40.3,161.2-40.3,1062.7v975.8l74.2-74.2C6693.5,4312.1,7179.3,3796.7,7730.8,3207z"/><path d="M3382.3,3166.7c-67.9-57.3-352.1-333-632.1-615.2c-405.2-409.4-502.7-515.5-481.5-536.7c19.1-19.1,131.5-29.7,364.9-33.9l339.4-6.4v-449.7c0-373.3,6.4-458.2,33.9-498.5c31.8-46.7,38.2-46.7,547.3-46.7c453.9,0,519.7,4.2,545.2,36.1c23.3,25.4,31.8,144.2,36,498.5l6.4,462.4h282.1c341.5,0,415.8,12.7,415.8,65.8c0,40.3-1026.7,1071.2-1170.9,1177.3C3571.1,3291.8,3520.2,3283.3,3382.3,3166.7z"/><path d="M2359.9-220.9c-27.6-19.1-61.5-67.9-78.5-106.1c-25.4-61.5-25.4-82.7,0-144.3c63.6-150.6-159.1-140,2723.6-140h2596.4l61.5,61.5c80.6,82.7,84.8,205.7,10.6,292.7l-50.9,59.4l-2607,6.4C2629.3-187,2406.6-189.1,2359.9-220.9z"/><path d="M2385.4-1502.1c-67.9-27.6-120.9-120.9-118.8-207.9c2.1-57.3,19.1-91.2,67.9-135.8l65.8-59.4h2600.6h2600.6l61.5,61.5c65.8,65.8,82.7,176.1,40.3,258.8c-59.4,108.2,50.9,103.9-2708.8,101.8C3564.8-1483,2410.8-1491.5,2385.4-1502.1z"/><path d="M2379-2813c-127.3-63.6-152.7-229.1-48.8-333l55.1-53h2607h2609.1l61.5,61.5c65.8,67.9,82.7,178.2,38.2,260.9c-55.2,103.9,14.8,101.8-2706.7,101.8C2616.6-2774.8,2449-2777,2379-2813z"/></g></g>
	</svg>';
    }
}

$attachment = new attachment();

if ($del = filter_input(INPUT_GET, 'del', FILTER_UNSAFE_RAW)) {
	$mes = array();
    if (! $attachment->delFiles($del)) {
		$mes['err'] = array(0 => 'QF_ERR_FILE_DEL');
    }
	$controller = new controller();
    $controller->redirect('attachment', $mes);
}

echo $attachment->ls_recursive(QF3_PLUGIN_DIR.'assets/attachment');

?>
</div>
</div>

<script>
function tgldispl(x){
	x=x.parentNode.querySelector('span').style;
	x.display=(x.display=='none')?'':'none';
}
</script>
