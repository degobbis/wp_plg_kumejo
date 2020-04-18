<?php

// Exit if accessed directly
if (!defined('WPINC'))
{
	die;
}

if (!function_exists('kumejo_options_validate'))
{
	function kumejo_options_validate($input)
	{
		$newInput = array();
		$defaults = array(
			'remote'  => '',
			'id'      => '',
			'sort'    => 'name',
			'sortdir' => 'desc',
			'limiter' => 4,
		);

		$options  = get_option('kumejo_options');
		$defaults = wp_parse_args($options, $defaults);

		$newInput['remote']  = kumejo_validate_field_remote($input['remote'], $defaults['remote']);
		$newInput['id']      = kumejo_validate_field_id($input['id'], $defaults['id']);
		$newInput['sort']    = kumejo_validate_field_sort($input['sort'], $defaults['sort']);
		$newInput['sortdir'] = kumejo_validate_field_sortdir($input['sortdir'], $defaults['sortdir']);
		$newInput['limiter'] = kumejo_validate_field_limiter($input['limiter'], $defaults['limiter']);

		$newInput = wp_parse_args($newInput, $defaults);

		return $newInput;
	}
}

if (!function_exists('kumejo_validate_field_remote'))
{
	function kumejo_validate_field_remote($input, $defaults)
	{
		if (empty($input))
		{
			$newInput = '';
		}
		else
		{
			$allowed = array("http", "https");

			$newInput = wp_strip_all_tags($input, true);
			$newInput = esc_url_raw($newInput, $allowed);

			if (empty($newInput))
			{
				$newInput = $defaults;
				add_settings_error('kumejo', 'kumejo', 'Die URL ist nicht erlaubt! Nur http:// oder https://');
			}
			else
			{
				$remote = parse_url($newInput);

				$newInput = $remote['scheme'] . '://' . $remote['host'] . $remote['path'];
			}
		}

		return $newInput;
	}
}

if (!function_exists('kumejo_validate_field_id'))
{
	function kumejo_validate_field_id($input, $defaults)
	{
		if (empty($input))
		{
			$newInput = '';
		}
		else
		{
			if (is_numeric($input))
			{
				$newInput = (int) $input;
			}
			else
			{
				$newInput = $defaults;
				add_settings_error('kumejo', 'kumejo', 'Die Benutzer-ID muss eine Zahl sein');
			}
		}

		return $newInput;
	}
}

if (!function_exists('kumejo_validate_field_sort'))
{
	function kumejo_validate_field_sort($input, $defaults)
	{
		$input    = sanitize_text_field($input);
		$allowed  = array('name', 'published_date');
		$newInput = $defaults;

		if (in_array($input, $allowed))
		{
			$newInput = (string) $input;
		}

		return $newInput;
	}
}

if (!function_exists('kumejo_validate_field_sortdir'))
{
	function kumejo_validate_field_sortdir($input, $defaults)
	{
		$input    = sanitize_text_field($input);
		$allowed  = array('asc', 'desc');
		$newInput = $defaults;

		if (in_array($input, $allowed))
		{
			$newInput = (string) $input;
		}

		return $newInput;
	}
}

if (!function_exists('kumejo_validate_field_limiter'))
{
	function kumejo_validate_field_limiter($input, $defaults)
	{
		$newInput = (int) $defaults;

		if (is_numeric($input))
		{
			$input = (int) $input;
			if ($input > 0 && $input < 11)
			{
				$newInput = (int) $input;
			}
		}

		return $newInput;
	}
}