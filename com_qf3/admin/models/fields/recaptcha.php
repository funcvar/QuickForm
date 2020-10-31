<?php
/**
* @package		Joomla
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldRecaptcha extends JFormField
{
    protected $type = 'recaptcha';

    protected function getInput()
    {
      return '<br><br>CAPTCHA: <a href="https://www.google.com/recaptcha" target="_blank">Google reCAPTCHA</a>';

    }
}
 ?>
