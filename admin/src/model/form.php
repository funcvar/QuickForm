<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;

\defined ('QF3_VERSION') or die;

class formModel extends baseModel {
  public function __construct () {
    $this->closelink = 'projects&task=forms&projectid=' . (int) $this->get ('projectid', $_POST);
    $this->savelink = 'projects&task=form.edit';
  }

  public function getItems () {
    $db = \JFactory::getDbo ();
    $db->setQuery ("SELECT * FROM #__qf3_forms WHERE id = " . (int) $this->get ('id', $_GET));
    return $db->loadObject ();
  }

  public function save () {
    $db = \JFactory::getDbo ();

    $data = filter_input (INPUT_POST, 'qffield', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $id = (int) $this->get ('id', $_GET, 0);

    if (! $data['title']) {
      $this->errors[] = 'The title is not filled in.';
      return $id;
    }

    if (qf::gettask () == 'form.savecopy') {
      $id = 0;
      $data['title'] = '- ' . $data['title'];
    }


    $projectid = (int) $this->get ('projectid', $_POST);
    if (! $projectid) {
      $this->errors[] = 'no project id';
      return;
    }
    $def = $id ? (int) $this->get ('def', $_POST) : $this->setDef ($projectid);

    $json = filter_input (INPUT_POST, 'fields', FILTER_DEFAULT);
    $ob = json_decode ($json);
    if ($ob === null) {
      $this->errors[] = 'json cannot be decoded.';
      return $id;
    }

    $inputData = array(
      'title' => '\'' . addslashes (strip_tags ($data['title'])) . '\'',
      'id' => (int) $id,
      'def' => (int) $def,
      'projectid' => (int) $projectid,
      'fields' => '\'' . addslashes ($json) . '\'',
    );

    if ($id) {
      foreach ($inputData as $key => $value) {
        $updates[] = $key . ' = ' . $value;
      }
      $db->setQuery ('UPDATE #__qf3_forms SET ' . implode (', ', $updates) . ' WHERE id=' . (int) $inputData['id']);
      $db->execute ();
      return $inputData['id'];
    }
    else {
      $db->setQuery ("INSERT INTO #__qf3_forms (" . implode (",", array_keys ($inputData)) . ") VALUES (" . implode (",", array_values ($inputData)) . ")");
      $db->execute ();
      return $db->insertid ();
    }
  }

  public function ajax () {
    $id = (int) $this->get ('id', $_POST);

    switch ($this->get ('mod', $_POST)) {
      case 'text': {
          echo Text::_ (strip_tags ($this->get ('str', $_POST)));
        }
        break;

      case 'selectors': {
          echo $this->getSelectors ($id);
        }
        break;

      case 'getForms': {
          echo $this->getForms ($id);
        }
        break;

      case 'fieldGroupTitle': {
          echo $this->fieldGroupTitle ($id);
        }
        break;
    }

  }

  protected function fieldGroupTitle ($id) {
    $db = \JFactory::getDbo ();

    if ($id) {
      $db->setQuery ('SELECT title FROM #__qf3_forms WHERE id = ' . (int) $id);
      $title = $db->loadResult ();
      return Text::_ ($title);
    }
    return '';
  }

  protected function getForms ($id) {
    $db = \JFactory::getDbo ();

    $db->setQuery ('SELECT id, title FROM #__qf3_forms WHERE projectid = ' . (int) $id);
    $forms = $db->loadObjectList ();
    $sections[] = '<option value="">' . Text::_ ('QF_NOT_SELECTED') . '</option>';
    foreach ($forms as $form) {
      $sections[] = '<option value="' . $form->id . '">' . Text::_ ($form->title) . '</option>';
    }

    return '<select id="filter_form" name="filter_form">' . implode ('', $sections) . '</select>';
  }

  protected function getSelectors ($id) {
    $db = \JFactory::getDbo ();

    $html = '<div class="qfselectors">';

    if ($id) {
      $db->setQuery ('SELECT projectid FROM #__qf3_forms WHERE id = ' . (int) $id);
      $projectid = $db->loadResult ();
    }
    else {
      $projectid = (int) $this->get ('projectid', $_POST);
    }


    $html .= '<div>' . Text::_ ('QF_PROGECTS') . ': ';
    $sections = array();

    $db->setQuery ('SELECT id, title FROM #__qf3_projects');
    $projects = $db->loadObjectList ();

    foreach ($projects as $project) {
      $selselected = (int) $project->id == (int) $projectid ? ' selected="selected"' : '';
      $sections[] = '<option value="' . $project->id . '"' . $selselected . '>' . Text::_ ($project->title) . '</option>';
    }

    $html .= '<select id="filter_project" name="filter_project">' . implode ('', $sections) . '</select>';
    $html .= '</div>';
    $html .= '<div>' . Text::_ ('QF_FIELD_GROUPS') . ': ';
    $sections = array();

    $db->setQuery ('SELECT id, title FROM #__qf3_forms WHERE projectid = ' . (int) $projectid);
    $forms = $db->loadObjectList ();

    $sections[] = '<option value="">' . Text::_ ('QF_NOT_SELECTED') . '</option>';

    foreach ($forms as $form) {
      $selselected = (int) $form->id == (int) $id ? ' selected="selected"' : '';
      $sections[] = '<option value="' . $form->id . '"' . $selselected . '>' . Text::_ ($form->title) . '</option>';
    }

    $html .= '<select id="filter_form" name="filter_form">' . implode ('', $sections) . '</select>';
    $html .= '</div>';

    $html .= '</div>';
    return $html;
  }

  protected function setDef ($projectid) {
    $db = \JFactory::getDbo ();
    $db->setQuery ('SELECT id FROM #__qf3_forms WHERE def = 1 AND projectid = ' . (int) $projectid);
    $def = $db->loadResult ();
    return ($def ? 0 : 1);
  }

}