<?php

/**
 * Server for ajax request of elang
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package	    mod
 * @subpackage  elang
 * @copyright   2013 University of La Rochelle, France
 * @license	    http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(__FILE__) . '/lib.php';
require_once dirname(__FILE__) . '/locallib.php';

$task = optional_param('task', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

// Detect if there is no course module id
if ($id == 0)
{
	header('HTTP/1.1 400 Bad Request');
	die;
}

// Get the course module, the elang instance and the context
$cm = get_coursemodule_from_id('elang', $id, 0, false);

// Detect if the course module exists
if (!$cm)
{
	header('HTTP/1.1 404 Not Found');
	die;
}

// Detect if the user is logged in
if (!isloggedin())
{
	header('HTTP/1.1 401 Unauthorized');
	die;
}


// Get the context
$context = context_module::instance($cm->id);

// Detect if the user has the capability to view this course module
if (!has_capability('mod/elang:view', $context))
{
	header('HTTP/1.1 403 Forbidden');
	die;
}

// Get the elang instance and the course
$course = $DB->get_record('course', array('id' => $cm->course), '*');
$elang = $DB->get_record('elang', array('id' => $cm->instance), '*');

// Detect an internal server error
if (!$course || !$elang)
{
	header('HTTP/1.1 500 Internal Server Error');
	die;
}

$options = json_decode($elang->options, true);
$repeatedunderscore = isset($options['repeatedunderscore']) ? $options['repeatedunderscore'] : 10;

switch ($task)
{
	case 'data':
		header('Content-type: application/json');
		$fs = get_file_storage();
		$files = $fs->get_area_files($context->id, 'mod_elang', 'videos', $elang->id);
		$sources = array();
		
		foreach ($files as $file)
		{
			if ($file->get_source())
			{
				$sources[] = array(
					'url' => (string) moodle_url::make_pluginfile_url(
						$file->get_contextid(),
						$file->get_component(),
						$file->get_filearea(),
						$file->get_itemid(),
						$file->get_filepath(),
						$file->get_filename()
					),
					'type' => $file->get_mimetype()
				);
			}
		}

		$files = $fs->get_area_files($context->id, 'mod_elang', 'poster', $elang->id);
		$poster = '';

		foreach ($files as $file)
		{
			if ($file->get_source())
			{
				$poster = (string) moodle_url::make_pluginfile_url(
					$file->get_contextid(),
					$file->get_component(),
					$file->get_filearea(),
					$file->get_itemid(),
					$file->get_filepath(),
					$file->get_filename()
				);
				break;
			}
		}

		$files = $fs->get_area_files($context->id, 'mod_elang', 'subtitle', $elang->id);
		$subtitle = '';

		foreach ($files as $file)
		{
			if ($file->get_source())
			{
				$path_parts = pathinfo($file->get_filename());
				$subtitle = (string) moodle_url::make_pluginfile_url(
					$file->get_contextid(),
					$file->get_component(),
					$file->get_filearea(),
					$file->get_itemid(),
					$file->get_filepath(),
					$path_parts['filename'] . '.vtt'
				);
				$pdf = (string) moodle_url::make_pluginfile_url(
					$file->get_contextid(),
					$file->get_component(),
					'pdf',
					$file->get_itemid(),
					$file->get_filepath(),
					$path_parts['filename'] . '.pdf'
				);
				break;
			}
		}

		$cues = array();
		$i = 0;
		$records = $DB->get_records('elang_cues', array('id_elang' => $elang->id), 'begin ASC');
		$users = $DB->get_records('elang_users', array('id_elang' => $elang->id, 'id_user' => $USER->id), '', 'id_cue,json');
 		foreach ($records as $id => $record)
		{
			$data = json_decode($record->json, true);
 	      	if (isset($users[$id]))
        	{
				$user = json_decode($users[$id]->json, true);
			}
			else
			{
				$user = array();
			}
			$elements = array();
			foreach ($data as $number => $element)
			{
				if ($element['type'] == 'input')
				{
					if (isset($user[$number]) && !empty($user[$number]['content']))
					{
						if ($user[$number]['content'] == $element['content'])
						{
							$elements[] = array(
								'type' => 'success',
								'content' => $element['content']
							);
						}
						else
						{
							$elements[] = array(
								'type' => 'failure',
								'size' => ((int) (mb_strlen($element['content'], 'UTF-8') - 1) / 10 + 1) * 10,
								'content' => $user[$number]['content']
							);
						}
					}
					elseif ($user[$number]['help'])
					{
						$elements[] = array(
							'type' => 'help',
							'content' => $element['content']
						);
					}
					else
					{
						$elements[] = array(
							'type' => 'input',
							'size' => ((int) (mb_strlen($element['content'], 'UTF-8') - 1) / 10 + 1) * 10,
							'content' => ''
						);
					}
				}
				else
				{
					$elements[] = array(
						'type' => 'text',
						'content' => $element['content']
					);
				}
			}

			$cues[] = array(
				'number' => $i++,
				'id' => $record->id,
				'title' => $record->title,
				'begin' => $record->begin / 1000,
				'end' => $record->end / 1000,
				'elements' => $elements
			);
		}

		echo json_encode(array(
			'title' => $elang->name,
			'description' => $elang->intro,
			'number' => 150,
			'success' => 50,
			'error' => 20,
			'help' => 10,
			'cues' => $cues,
			'page' => 10,
			'sources' => $sources,
			'poster' => $poster,
			'track' => $subtitle,
			'pdf' => $pdf,
			'language' => $elang->language
		));
		die;
		break;
	case 'check':
		$id_cue = optional_param('id_cue', 0, PARAM_INT);
		$cue = $DB->get_record('elang_cues', array('id' => $id_cue), '*');
		if (!$cue)
		{
			header('HTTP/1.1 404 Not Found');
			die;
		}
		if ($cue->id_elang != $elang->id)
		{
			header('HTTP/1.1 400 Bad Request');
			die;
		}
		$number = optional_param('number', 0, PARAM_INT);
		$elements = json_decode($cue->json, true);
		if (!isset($elements[$number]))
		{
			header('HTTP/1.1 500 Internal Server Error');
			die;
		}

		$text = optional_param('text', '', PARAM_TEXT);

		// Log action
		add_to_log($course->id, 'elang', 'view check ' . $cue->number . ' ' . $elements[$number]['order'] . ' [' . $text . ']=[' . $elements[$number]['content'] . ']', 'view.php?id=' . $cm->id, $cm->name, $cm->id);

		header('Content-type: application/json');
		$user = $DB->get_record('elang_users', array('id_cue' => $id_cue, 'id_user' => $USER->id));
		if ($user)
		{
			$data = json_decode($user->json, true);
			$data[$number] = array('help' => false, 'content' => $text);
			$user->json = json_encode($data);
			$DB->update_record('elang_users', $user);
		}
		else
		{
			$data = array($number => array('help' => false, 'content' => $text));
			$DB->insert_record(
				'elang_users',
				array(
					'id_cue' => $id_cue,
					'id_elang' => $elang->id,
					'id_user' => $USER->id,
					'json' => json_encode($data),
				)
			);
		}

		$cue_text = Elang\generateCueText($elements, $data, '-', $repeatedunderscore);
		if ($elements[$number]['content'] == $text)
		{
			echo json_encode(array('status' => 'success', 'cue' => $cue_text, 'content' => $text));
		}
		else
		{
			echo json_encode(array('status' => 'failure', 'cue' => $cue_text));
		}
		die;
		break;
	case 'help':
		$id_cue = optional_param('id_cue', 0, PARAM_INT);
		$cue = $DB->get_record('elang_cues', array('id' => $id_cue), '*');
		if (!$cue)
		{
			header('HTTP/1.1 404 Not Found');
			die;
		}
		if ($cue->id_elang != $elang->id)
		{
			header('HTTP/1.1 400 Bad Request');
			die;
		}
		$number = optional_param('number', 0, PARAM_INT);
		$elements = json_decode($cue->json, true);
		if (!isset($elements[$number]))
		{
			header('HTTP/1.1 500 Internal Server Error');
			die;
		}

		// Log action
		add_to_log($course->id, 'elang', 'view help ' . $cue->number . ' ' . $elements[$number]['order'] . ' [' . $elements[$number]['content'] . ']', 'view.php?id=' . $cm->id. $task, $cm->name, $cm->id);

		header('Content-type: application/json');
		$user = $DB->get_record('elang_users', array('id_cue' => $id_cue, 'id_user' => $USER->id));
		if ($user)
		{
			$data = json_decode($user->json, true);
			$data[$number] = array('help' => true, 'content' => '');
			$user->json = json_encode($data);
			$DB->update_record('elang_users', $user);
		}
		else
		{
			$data = array($number => array('help' => true, 'content' => ''));
			$DB->insert_record(
				'elang_users',
				array(
					'id_cue' => $id_cue,
					'id_elang' => $elang->id,
					'id_user' => $USER->id,
					'json' => json_encode($data),
				)
			);
		}

		$cue_text = Elang\generateCueText($elements, $data, '-', $repeatedunderscore);
		echo json_encode(array('cue' => $cue_text, 'content' => $elements[$number]['content']));
		die;
		break;
	default:
		header('HTTP/1.1 400 Bad Request');
		die;
		break;
}