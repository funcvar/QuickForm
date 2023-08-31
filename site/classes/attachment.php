<?php
/**
 * @Copyright ((c) plasma-web.ru
         * @license    GPLv2 or later
         */

namespace QuickForm;

\defined('QF3_VERSION') or die;

require_once __DIR__.'/qfcart.php';

class qfAttachment extends qfCart
{
    protected $path = JPATH_COMPONENT.'/assets/attachment/';

    public function showAttachmentBox()
    {
        $html  = '';
        $fl ='';
        $accept = '';
        $num = (int) qf::get('num', $_POST);
        $folder = $this->getFolderName($num);

        $html  .= '<div class="atch_box_inner">';
        $html .= '<div class="atch_title"><h2>QF_ATTACHMENT</h2></div>';

        $html .= '<div class="atch_area">QF_ATCH_DROP';
        $whitelist = $this->getwhitelist();
        if ($whitelist) {
            $accept = ' data-accept="'.implode(',', $whitelist).'"';
            $html .= '<br>('.implode(', ', $whitelist) . ')';
        }
        $html .= '<br>QF_MAX_FILESIZE ' . get_cfg_var('upload_max_filesize');
        $html .= '</div>';

        $html .= '<div style="display:none"><input id="file_field" type="file" multiple'.$accept.'></div>';
        $html .= '<div class="atch_message"></div>';
        $html .= '<div class="filelisting">';

        if (qf::get('imgs', $folder)) {
            $path = $this->path. $folder['imgs'] .'/';
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {
                        $html .= '<div class="imgtbox_old"><span class="del_old" data-href="'.$folder['imgs'].'/'.$file.'">'.$file.'</span><span class="imgtdel del_old_img">✕</span></div>';
                        $fl=1;
                    }
                }
            }
        }
        $html .= '</div>';
        $html .= '<div class="atch_links">';
        $html .= '<div class="atch_links_title">QF_LINK_LBL</div>';

        $lnk = mb_strtolower(Text::_('QF_LINK'), 'UTF-8');
        if (qf::get('links', $folder)) {
            foreach ($folder['links'] as $link) {
                $html .= '<div class="atch_link"><input type="text" placeholder="'.$lnk.'" value="'.$link.'"></div>';
            }
            $fl=1;
        } else {
            $html .= '<div class="atch_link"><input type="text" placeholder="'.$lnk.'"></div>';
        }

        $html .= '<div class="atch_link_more"><a href="javascript:void(0)">QF_ADD_LINK</a></div>';
        $html .= '</div>';
        $html .= '<div class="atch_coment_title">QF_COMMENT_TO</div>';
        $html .= '<div class="atch_coment">';
        $html .= '<textarea>'.(isset($folder['coment']) ? $folder['coment'] : '').'</textarea>';
        $html .= '</div>';
        $html .= '<div class="atch_btns">';
        if ($fl || (isset($folder['coment']) && $folder['coment'])) {
            $html .= '<div class="atch_btn_send"><a href="javascript:void(0)" class="atch_send">QF_SAVE</a></div>';
        } else {
            $html .= '<div class="atch_btn_send"><a href="javascript:void(0)" class="atch_send">QF_DOWNLOAD</a></div>';
        }
        $html .= '<div class="atch_btn_reset"><a href="javascript:void(0)" class="atch_reset">QF_CANCEL</a></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return Text::translate($html);
    }



    public function sessionLoading()
    {
        if (! $this->cart) {
            return Text::_('QF_ERR_SES');
        }

        $num = (int) qf::get('num', $_POST);
        if ($this->attachment == 2) {
            if (! isset($this->cart[$num])) {
                return Text::_('QF_ERR_CART');
            }
        }

        $files = array();
        $blacklist = $this->extBlacklist();
        $whitelist = $this->getwhitelist();
        $rfiles = $_FILES;

        if (isset($rfiles['imagefile']['name'])) {
            foreach ($rfiles['imagefile']['name'] as $k=>$file) {
                if ($file && str_replace($blacklist, '', strtolower($file)) != strtolower($file)) {
                    return Text::_('QF_ERR_FILE_NAME') . ' ' . htmlspecialchars($file);
                }
                if ($whitelist) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    if (! in_array(strtolower($ext), $whitelist)) {
                        return Text::_('QF_ERR_FILE_NAME') . ' ' . htmlspecialchars($file);
                    }
                }
                if (preg_replace('/[\/:*?"<>|+%!@]/', '', $file) != $file) {
                    return Text::_('QF_ERR_FILE_NAME') . ': '. htmlspecialchars($file);
                }

                $files[] = array('name' => $file, 'tmp_name' => $rfiles['imagefile']['tmp_name'][$k]);
            }
        }

        $links = filter_input(INPUT_POST, 'imagelinks', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
        $coment = filter_input(INPUT_POST, 'imagecoment', FILTER_SANITIZE_SPECIAL_CHARS);

        $folder = (array) $this->getFolderName($num);

        if (! $files && ! $links && ! $coment) {
            if (! qf::get('imgs', $folder) && ! qf::get('links', $folder) && ! qf::get('coment', $folder)) {
                return Text::_('QF_ERR_ATTACH_FILES');
            }
        }

        $oldfiles = $this->checkFilesInUserFolder($num);
        if (! $files && ! $oldfiles) {
            $folder['imgs'] = false;
        }

        if ($this->attachment == 1) {
            $folder['links'] = $links;
            $folder['coment'] = $coment;
            qf::ses()->set('qfcartimg', $folder);
        } elseif ($this->attachment == 2) {
            $this->cart[$num]['links'] = $links;
            $this->cart[$num]['coment'] = $coment;
            $this->cart[$num]['imgs'] = qf::get('imgs', $folder);
            qf::ses()->set('qfcartbox', $this->cart);
        }

        if ($files) {
            return $this->filesLoading($files, $num);
        }

        return 'label: ' . $this->getCartAttachmentHtml($num);
    }




    protected function filesLoading($files, $num)
    {
        $folder = $this->getNewFolder($num, time());
        if (! $folder) {
            return 'error: Failed to create folder';
        }
        $orig_directory = $this->path.$folder."/";
        foreach ($files as $file) {
            if (! move_uploaded_file($file ['tmp_name'], $orig_directory.$file ['name'])) {
                return 'error: Failed to load file: ' . htmlspecialchars($file ['name']);
            }
        }
        return 'label: <i class="fa fa-check" aria-hidden="true"></i><a href="javascript:void(0)">'.Text::_('QF_SUCCESS_FILES').'</a>';
    }




    protected function getNewFolder($num, $tm)
    {
        static $fl=0;
        $folder = $this->getFolderName($num);
        if (! qf::get('imgs', $folder) || !$this->folder_exist($this->path.$folder['imgs'])) {
            $path = $this->path.$tm.'/';
            if (! $this->folder_exist($path)) {
                mkdir($path, 0777);
                if ($this->attachment == 1) {
                    $cartfiles = qf::ses()->get('qfcartimg', []);
                    $cartfiles['imgs'] = $tm;
                    qf::ses()->set('qfcartimg', $cartfiles);
                } elseif ($this->attachment == 2) {
                    $this->cart[$num]['imgs'] = $tm;
                    qf::ses()->set('qfcartbox', $this->cart);
                }
                return $tm;
            } else {
                $tm++;
                $fl++;
                if ($fl>10) {
                    return;
                }
                return $this->getNewFolder($num, $tm);
            }
        }
        return $folder['imgs'];
    }




    protected function folder_exist($folder)
    {
        $path = realpath($folder);
        return ($path !== false and is_dir($path)) ? true : false;
    }




    public function attachment_del_img()
    {
        $num = (int) qf::get('num', $_POST);
        $folder = $this->getFolderName($num);

        if (! $folder) {
            return Text::_('QF_ERR_SES_DEL');
        }

        $pats = explode('/', filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        if (sizeof($pats)==2) {
            if ($folder['imgs'] && $folder['imgs'] == $pats[0]) {
                $file1 = $this->path. $folder['imgs'] .'/'.$pats[1];
                if (file_exists($file1)) {
                    unlink($file1);
                } else {
                    return Text::_('QF_ERR_FILE_DEL');
                }

                if ([] === (array_diff(scandir($this->path. $folder['imgs'] .'/'), array('.', '..')))) {
                    $this->recursiveRemoveDir($this->path. $folder['imgs']);
                }

                return 'yes';
            }
        }
    }




    protected function recursiveRemoveDir($dir)
    {
        $includes = new \FilesystemIterator($dir);

        foreach ($includes as $include) {
            if (is_dir($include) && !is_link($include)) {
                $this->recursiveRemoveDir($include);
            } else {
                unlink($include);
            }
        }

        rmdir($dir);
    }




    protected function getFolderName($num)
    {
        $folder = [];
        if ($this->attachment == 1) {
            $folder = qf::ses()->get('qfcartimg', []);
        } elseif ($this->attachment == 2) {
            if (isset($this->cart[$num])) {
                $folder = $this->cart[$num];
            }
        }
        return $folder;
    }




    protected function checkFilesInUserFolder($num)
    {
        $folder = $this->getFolderName($num);
        if (qf::get('imgs', $folder)) {
            $path = $this->path. $folder['imgs'] .'/';
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {
                        return true;
                    }
                }
            }
        }
    }




    protected function getwhitelist()
    {
        $whitelist = array();
        if ($this->shop['accept'] == 1) {
            $whitelist = $this->shop['whitelist'];
            $whitelist = preg_replace('/\s/', '', $whitelist);
            $whitelist = explode(',', $whitelist);
        }
        return $whitelist;
    }




    public function getCartAttachmentHtml($num)
    {
        $folder = $this->getFolderName($num);
        $files = $this->checkFilesInUserFolder($num);

        if ($files || qf::get('links', $folder) || qf::get('coment', $folder)) {
            return '<i class="fa fa-check" aria-hidden="true"></i><a href="javascript:void(0)">'.Text::_('QF_SUCCESS_FILES').'</a>';
        } else {
            $whitelist = $this->getwhitelist();
            if ($whitelist) {
                $whitelist = ' ('.implode(', ', $whitelist) . ')';
            } else {
                $whitelist = '';
            }

            if ($this->shop['reqfiles'] == 1) {
                $req = ' data-req="1"';
                $whitelist .= ' *';
            } else {
                $req = '';
            }

            return '<i class="fa fa-upload" aria-hidden="true"></i><a href="javascript:void(0)"'.$req.'>'.Text::_('QF_ATTACHMENT').$whitelist.'</a>';
        }
    }




    public function getEmailAttachmentHtml($num=-1)
    {
        $html = '';
        $fl = false;
        $folder = $this->getFolderName($num);
        $html .= '<br><table border="1" width="100%" style="border-color:#e7e7e7;" cellpadding="5" cellspacing="0">';
        $html .= '<tr>';
        $html .= '<td>';

        if (qf::get('imgs', $folder)) {
            $files = scandir($this->path. $folder['imgs'] .'/');
            $html .= '<div>';
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $html .= '<a href="'.QF3_PLUGIN_URL.'assets/attachment/'. $folder['imgs'] .'/'.$file.'">'.$file.'</a><br>';
                    $fl = true;
                }
            }
            $html .= '</div><br>';
        }

        if (qf::get('links', $folder)) {
            foreach ($folder['links'] as $link) {
                $html .= '<div>QF_LINK: '.$link.'</div>';
                $fl = true;
            }
            $html .= '<br>';
        }

        if (qf::get('coment', $folder)) {
            $html .= '<div>QF_COMMENT<br>'.$folder['coment'].'</div>';
            $fl = true;
        }

        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        if ($this->shop['reqfiles'] == 1) {
            if (! $fl) {
                return 'ERR_REQ_FILES';
            }
        }

        if (! $fl) {
            return '';
        } else {
            return $html;
        }
    }
}
