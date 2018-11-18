<?php
/* @Copyright ((c) plasma-web.ru
October 21, 2018
 */

 defined('_JEXEC') or die;

class plgContentQf3 extends JPlugin
{
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        if ($context === 'com_finder.indexer') {
            return true;
        }

        if (strpos($row->text, '{QF3') === false) {
            return true;
        }

        require_once('components/com_qf3/classes/buildform.php');
        $qf = new QuickForm3();

        preg_match_all('/{QF3\s?=\s?(\d*?)}/', $row->text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $form = $qf->getQuickForm($match[1]);
            $row->text 	= str_replace($match[0], $form, $row->text);
        }
    }
}
