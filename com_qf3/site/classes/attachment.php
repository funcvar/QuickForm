<?php
/**
 * @package		Joomla & QuickForm
* @Copyright ((c) juice-lab.ru
        * @license    GNU/GPL
        */
defined('_JEXEC') or die();

require_once JPATH_COMPONENT.'/classes/qfcart.php';

class qfAttachment extends qfCart
{
    protected $path = JPATH_COMPONENT.'/assets/attachment/';

    public function showAttachmentBox()
    {
        $html  = '';
        $fl ='';
        $accept = '';
        $num = $this->app->input->get('num', -1, 'int');
        $folder = $this->getFolderName($num);

        $html  .= '<div class="atch_box_inner">';
        $html .= '<div class="atch_title"><h2>'.JText::_('QF_ATTACHMENT').'</h2></div>';

        $html .= '<div class="atch_area">'.JText::_('QF_ATCH_DROP');
        $whitelist = $this->getwhitelist();
        if ($whitelist) {
            $accept = ' data-accept="'.implode(',', $whitelist).'"';
            $html .= '<br>('.implode(', ', $whitelist) . ')';
        }
        $html .= '<br>'.JText::_('QF_MAX_FILESIZE').' ' . get_cfg_var('upload_max_filesize');
        $html .= '</div>';

        $html .= '<div style="display:none"><input id="file_field" type="file" multiple'.$accept.'></div>';
        $html .= '<div class="atch_message"></div>';
        $html .= '<div class="filelisting">';

        if ($this->get('imgs', $folder)) {
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
        $html .= '<div class="atch_links_title">'.JText::_('QF_LINK_LBL').'</div>';
        if ($this->get('links', $folder)) {
            foreach ($folder['links'] as $link) {
                $html .= '<div class="atch_link"><input type="text" placeholder="'.JText::_('QF_LINK').'" value="'.$link.'"></div>';
            }
            $fl=1;
        } else {
            $html .= '<div class="atch_link"><input type="text" placeholder="'.JText::_('QF_LINK').'"></div>';
        }
        $html .= '<div class="atch_link_more"><a href="javascript:void(0)">'.JText::_('QF_ADD_LINK').'</a></div>';
        $html .= '</div>';
        $html .= '<div class="atch_coment_title">'.JText::_('QF_COMMENT_TO').'</div>';
        $html .= '<div class="atch_coment">';
        $html .= '<textarea>'.(isset($folder['coment'])?$folder['coment']:'').'</textarea>';
        $html .= '</div>';
        $html .= '<div class="atch_btns">';
        if ($fl || (isset($folder['coment']) && $folder['coment'])) {
            $html .= '<div class="atch_btn_send"><a href="javascript:void(0)" class="atch_send">'.JText::_('QF_SAVE').'</a></div>';
        } else {
            $html .= '<div class="atch_btn_send"><a href="javascript:void(0)" class="atch_send">'.JText::_('QF_DOWNLOAD').'</a></div>';
        }
        $html .= '<div class="atch_btn_reset"><a href="javascript:void(0)" class="atch_reset">'.JText::_('QF_CANCEL').'</a></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }



    public function sessionLoading()
    {
        $cart = $this->session->get('qfcartbox');

        if (!$cart) {
            return JText::_('QF_ERR_SES');
        }

        $num = $this->app->input->get('num', -1, 'int');
        if ($this->attachment == 2) {
            if ($num == -1 || !isset($cart[$num])) {
                return JText::_('QF_ERR_CART');
            }
        }

        $files = array();
        $blacklist = array('.php', '.cgi', '.pl', '.fcgi', '.scgi', '.fpl', '.phtml', '.asp', '.jsp', '.py', '.exe', '.htm', '.htaccess', '.htpasswd', '.ini', '.sh');
        $whitelist = $this->getwhitelist();

        if (isset($_FILES['imagefile']['name'])) {
            foreach ($_FILES['imagefile']['name'] as $k=>$file) {
                if ($file && str_replace($blacklist, '', strtolower($file)) != strtolower($file)) {
                    return JText::_('QF_ERR_FILE_NAME') . ' ' . $file;
                }
                if ($whitelist) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    if (!in_array(strtolower($ext), $whitelist)) {
                        return JText::_('QF_ERR_FILE_EXT') . ' ' . $file;
                    }
                }

                $files[] = array('name' => $file, 'tmp_name' => $_FILES['imagefile']['tmp_name'][$k]);
            }
        }

        $links = $this->app->input->get('imagelinks', array(), 'array');
        $coment = $this->app->input->get('imagecoment', '', 'str');

        $folder = $this->getFolderName($num);

        if (!$files && !$links && !$coment) {
            if (!$this->get('imgs', $folder) && !$this->get('links', $folder) && !$this->get('coment', $folder)) {
                return JText::_('QF_ERR_ATTACH_FILES');
            }
        }

        $oldfiles = $this->checkFilesInUserFolder($num);
        if (!$files && !$oldfiles) {
            $folder['imgs'] = false;
        }

        if ($this->attachment == 1) {
            $folder['links'] = $links;
            $folder['coment'] = $coment;
            $this->session->set('qfcartimg', $folder);
        } elseif ($this->attachment == 2) {
            $cart[$num]['links'] = $links;
            $cart[$num]['coment'] = $coment;
            $cart[$num]['imgs'] = $this->get('imgs', $folder);
            $this->session->set('qfcartbox', $cart);
        }

        if ($files) {
            return $this->filesLoading($files, $num);
        }

        return 'label: ' . $this->getCartAttachmentHtml($num);
    }




    protected function filesLoading($files, $num)
    {
        $folder = $this->getNewFolder($num, time());
        if (!$folder) {
            return JText::_('QF_ERR_FOLDER');
        }
        $orig_directory = $this->path.$folder."/";
        foreach ($files as $file) {
            if (!move_uploaded_file($file ['tmp_name'], $orig_directory.$file ['name'])) {
                return JText::_('QF_ERR_DOWNLOAD') . ' ' . $file ['name'];
            }
        }
        return 'label: <i class="fa fa-check" aria-hidden="true"></i><a href="javascript:void(0)">'.JText::_('QF_SUCCESS_FILES').'</a>';
    }




    protected function getNewFolder($num, $tm)
    {
        static $fl=0;
        $folder = $this->getFolderName($num);
        if (!$this->get('imgs', $folder) || !$this->folder_exist($this->path.$folder['imgs'])) {
            $path = $this->path.$tm.'/';
            if (!$this->folder_exist($path)) {
                mkdir($path, 0777);
                if ($this->attachment == 1) {
                    $cartfiles = $this->session->get('qfcartimg');
                    $cartfiles['imgs'] = $tm;
                    $this->session->set('qfcartimg', $cartfiles);
                } elseif ($this->attachment == 2) {
                    $cart = $this->session->get('qfcartbox');
                    $cart[$num]['imgs'] = $tm;
                    $this->session->set('qfcartbox', $cart);
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
        $num = $this->app->input->get('num', -1, 'int');
        $folder = $this->getFolderName($num);

        if (!$folder) {
            return JText::_('QF_ERR_SES_DEL');
        }

        $pats = explode('/', $this->app->input->get('name', '', 'str'));
        if (sizeof($pats)==2) {
            if ($folder['imgs'] && $folder['imgs'] == $pats[0]) {
                $file1 = $this->path. $folder['imgs'] .'/'.$pats[1];
                if (file_exists($file1)) {
                    unlink($file1);
                } else {
                    return JText::_('QF_ERR_FILE_DEL');
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
        $includes = new FilesystemIterator($dir);

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
        $folder = false;
        if ($this->attachment == 1) {
            $folder = $this->session->get('qfcartimg');
        } elseif ($this->attachment == 2) {
            $cart = $this->session->get('qfcartbox');
            if (isset($cart[$num])) {
                $folder = $cart[$num];
            }
        }
        return $folder;
    }




    protected function checkFilesInUserFolder($num)
    {
        $folder = $this->getFolderName($num);
        if ($this->get('imgs', $folder)) {
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
        if ($this->shopParams['accept'] == 1) {
            $whitelist = $this->shopParams['whitelist'];
            $whitelist = preg_replace('/\s/', '', $whitelist);
            $whitelist = explode(',', $whitelist);
        }
        return $whitelist;
    }




    public function getCartAttachmentHtml($num)
    {
        $folder = $this->getFolderName($num);
        $files = $this->checkFilesInUserFolder($num);

        if ($files || $this->get('links', $folder) || $this->get('coment', $folder)) {
            return '<i class="fa fa-check" aria-hidden="true"></i><a href="javascript:void(0)">'.JText::_('QF_SUCCESS_FILES').'</a>';
        } else {
            $whitelist = $this->getwhitelist();
            if ($whitelist) {
                $whitelist = ' ('.implode(', ', $whitelist) . ')';
            } else {
                $whitelist = '';
            }

            if ($this->shopParams['reqfiles'] == 1) {
                $req = ' data-req="1"';
                $whitelist .= ' *';
            } else {
                $req = '';
            }

            return '<i class="fa fa-upload" aria-hidden="true"></i><a href="javascript:void(0)"'.$req.'>'.JText::_('QF_ATTACHMENT').$whitelist.'</a>';
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

        if ($this->get('imgs', $folder)) {
            $files = scandir($this->path. $folder['imgs'] .'/');
            $html .= '<div>';
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $html .= '<a href="'.JUri::root().'components/com_qf3/assets/attachment/'. $folder['imgs'] .'/'.$file.'">'.$file.'</a><br>';
                    $fl = true;
                }
            }
            $html .= '</div><br>';
        }

        if ($this->get('links', $folder)) {
            foreach ($folder['links'] as $link) {
                $html .= '<div>'.JText::_('QF_LINK2').' '.$link.'</div>';
                $fl = true;
            }
            $html .= '<br>';
        }

        if ($this->get('coment', $folder)) {
            $html .= '<div>'.JText::_('QF_COMMENT').'<br>'.$folder['coment'].'</div>';
            $fl = true;
        }

        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        if ($this->shopParams['reqfiles'] == 1) {
            if (!$fl) {
                return 'ERR_REQ_FILES';
            }
        }

        if (!$fl) {
            return '';
        } else {
            return $html;
        }
    }
}
