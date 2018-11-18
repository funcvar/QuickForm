<?php
/**
* @package		Joomla
* @Copyright ((c) plasma-web.ru
* @license    GNU/GPL
*/
defined('JPATH_PLATFORM') or die;

class JFormFieldCalculatordesk extends JFormField
{
    protected $type = 'calculatordesk';

    protected function getInput() {
      $html = '';
      $html .= '<div class="cdesk cdesk_0">';
      $html .= '</div>';

      $html .= '<div class="cdesk cdesk_default">';
      $html .= JText::_('QF_CDESK_DEFAULT');
      $html .= '</div>';

      $html .= '<div class="cdesk cdesk_multipl">';
      $html .= JText::_('QF_CDESK_MULTIPL');
      $html .= '</div>';

      $html .= '<div class="cdesk cdesk_simple">';
      $html .= JText::_('QF_CDESK_SIMPLE');
      $html .= '</div>';

      $html .= '<div class="cdesk cdesk_custom">';
      $html .= JText::_('QF_CDESK_CUSTOM');
        $html .= '<pre>
        $customQFcalculator = function($project, $data)
        {
          $r = 0; // cone radius
          $h = 0; // cone height

          foreach($data as $field){
            if(isset($field->math)){
              if($field->math == \'radius\'){
                $r = (float) $field->value;
              }
              elseif($field->math == \'height\'){
                $h = (float) $field->value;
              }
            }
          }

          $l = sqrt($h*$h + $r*$r);
          $calculatorSum[\'42.7\'] = pi()*$r*($l + $r); //  cone area
          $calculatorSum[\'42.8\'] = pi()*$r*$r*$h/3; // cone volume

          return $calculatorSum;
        };

        </pre>';
      $html .= '</div>';

      return $html;
    }

    protected function getLabel() {
      return;
    }
}
?>
