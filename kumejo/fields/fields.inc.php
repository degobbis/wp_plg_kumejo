<?php

// Exit if accessed directly
if (!defined('WPINC'))
{
	die;
}

/**
 * Include fields validation
 */
include_once dirname(__FILE__) . '/fields.validation.inc.php';

/**
 * Get default valuse
 */
function kumejo_get_default_options()
{
	return array(
		'remote'  => '',
		'id'      => '',
		'sort'    => 'published_date',
		'sortdir' => 'desc',
		'limiter' => '4',
	);
}

/**
 * Init kumejo_admin_init() to load fields
 */
if (!function_exists('kumejo_admin_init'))
{
	function kumejo_admin_init()
	{
		if (! is_admin()) {
			return;
		}

		if (is_multisite())
		{
			if (is_network_admin())
			{
				return;
			}
		}

		$options = get_option('kumejo_options');

		if (empty($options))
		{
			update_option('kumejo_options', kumejo_get_default_options());
		}

		add_settings_section('kumejo_section', 'KuMeJo Settings', 'kumejo_section_text', 'kumejo');
		add_settings_field('kumejo_field_remote_url', 'URL zum Abholen der Jobs', 'kumejo_field_remote_url', 'kumejo', 'kumejo_section', array('label_for' => 'kumejo_field_remote_url'));
		add_settings_field('kumejo_field_id', 'Ihre Benutzer-ID', 'kumejo_field_id', 'kumejo', 'kumejo_section', array('label_for' => 'kumejo_field_id'));
		add_settings_field('kumejo_field_sort', 'Nach was soll sortiert werden', 'kumejo_field_sort', 'kumejo', 'kumejo_section', array('label_for' => 'kumejo_field_sort'));
		add_settings_field('kumejo_field_sortdir', 'Sortier-Reihenfolge', 'kumejo_field_sortdir', 'kumejo', 'kumejo_section', array('label_for' => 'kumejo_field_sortdir'));
		add_settings_field('kumejo_field_limiter', 'Anzahl der Jobs pro Seite', 'kumejo_field_limiter', 'kumejo', 'kumejo_section', array('label_for' => 'kumejo_field_limiter'));
		register_setting('kumejo-settings', 'kumejo_options', 'kumejo_options_validate');
	}
}
add_action('admin_init', 'kumejo_admin_init');

if (!function_exists('kumejo_section_text'))
{
	function kumejo_section_text()
	{
		echo '<p>Alle nötigen Informationen erhalten Sie von Ihrem Anbieter, oder finden Sie auf Ihrer Profilseite beim Anbieter.</p>';
	}
}

if (!function_exists('kumejo_field_remote_url'))
{
	function kumejo_field_remote_url()
	{
		$options = get_option('kumejo_options'); ?>
		<input id="kumejo_field_remote_url" name="kumejo_options[remote]" size="60" type="text"
			   value="<?php echo $options['remote']; ?>"/>
		<?php
	}
}

if (!function_exists('kumejo_field_id'))
{
	function kumejo_field_id()
	{
		$options = get_option('kumejo_options'); ?>
		<input id="kumejo_field_id" name="kumejo_options[id]" size="4" type="text"
			   value="<?php echo $options['id']; ?>"/>
		<?php
	}
}

if (!function_exists('kumejo_field_sort'))
{
	function kumejo_field_sort()
	{
		$options                 = get_option('kumejo_options');
		$name_selected           = $options['sort'] == 'name' ? ' selected="selected"' : '';
		$published_date_selected = $options['sort'] == 'published_date' ? ' selected="selected"' : '';
		?>
		<select id="kumejo_field_sort" name="kumejo_options[sort]">
			<option value="name"<?php echo $name_selected; ?>>Name</option>
			<option value="published_date"<?php echo $published_date_selected; ?>>Erstellungsdatum</option>
		</select>
		<?php
	}
}

if (!function_exists('kumejo_field_sortdir'))
{
	function kumejo_field_sortdir()
	{
		$options       = get_option('kumejo_options');
		$asc_selected  = $options['sortdir'] == 'asc' ? ' checked="checked"' : '';
		$desc_selected = $options['sortdir'] == 'desc' ? ' checked="checked"' : '';
		?>
		<fieldset id="kumejo_field_sortdir">
			<input type="radio" id="kumejo_field_sortdir0" name="kumejo_options[sortdir]"
				   value="asc"<?php echo $asc_selected; ?>>
			<label for="kumejo_field_sortdir0">aufsteigend</label>
			<br/>
			<input type="radio" id="kumejo_field_sortdir1" name="kumejo_options[sortdir]"
				   value="desc"<?php echo $desc_selected; ?>>
			<label for="kumejo_field_sortdir1">absteigend</label>
		</fieldset>
		<?php
	}
}

if (!function_exists('kumejo_field_limiter'))
{
	function kumejo_field_limiter()
	{
		$options = get_option('kumejo_options');
		?>
		<input id="kumejo_field_limiter" class="small-text" name="kumejo_options[limiter]" type="number" step="1"
			   min="1" max="10" value="<?php echo $options['limiter']; ?>"/>
		<?php
	}
}
