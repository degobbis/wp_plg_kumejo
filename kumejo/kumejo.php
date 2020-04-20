<?php
/*
 * Plugin Name: Kumejo SERP
 * Plugin URI: https://github.com/degobbis/wp_plg_kumejo
 * Description: This Plugin implements kumejo.de SERP as an iFrame.
 * Version: 1.0-rc1
 * Author: Guido De Gobbis - Kunze Medien AG
 * Author URI: http://www.kunze-medien.de
 * License: GPLv3
 * Text Domain: kumejo
 *
 * Copyright 2020  Kunze Medien AG
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Exit if accessed directly
if (!defined('WPINC'))
{
	die;
}

/**
 * Include update logic
 */
include dirname(__FILE__) . '/update.php';

/**
 * Define if output is set
 *
 * @param bool $isset
 *
 * @return   bool
 */
function kumejo_isset($isset = false)
{
	static $return;

	if (null === $return)
	{
		$return = false;
	}

	if (true === $isset)
	{

		$return = true;
	}

	return $return;
}

/**
 * Add js and css
 */
function kumejo_add_css_js()
{
	if (is_admin() || kumejo_isset())
	{
		return;
	}
	?>
	<script type="text/javascript" src="<?php echo plugins_url('js/iframeResizer.min.js', __FILE__); ?>"></script>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo plugins_url('css/kumejo.css', __FILE__); ?>">
	<?php
}

add_action('wp_head', 'kumejo_add_css_js');

/**
 * Include fields
 */
include_once dirname(__FILE__) . '/fields/fields.inc.php';

/**
 * Register uninstall hooks.
 */
if (!function_exists('kumejo_options_validate'))
{
	function kumejo_uninstall_hook()
	{
		if (is_multisite())
		{
			return;
		}

		delete_option('kumejo_settings');
	}
}
register_uninstall_hook(__FILE__, 'kumejo_uninstall_hook');

/**
 * Add menu item for admin page
 */
if (!function_exists('kumejo_add_admin_page'))
{
	function kumejo_add_admin_page()
	{
		add_menu_page(__('KuMeJo', 'kumejo'), __('KuMeJo'), 'manage_options', 'kumejo', 'kumejo_admin_page');
	}
}
add_action('admin_menu', 'kumejo_add_admin_page');

/**
 * Admin Page
 */
if (!function_exists('kumejo_admin_page'))
{
	function kumejo_admin_page()
	{
		$error   = array();
		$options = get_option('kumejo_options');

		if (empty($options['remote']))
		{
			$error[] = __('Die URL zum Abholen der Jobs darf nicht leer sein!');
		}

		if (empty($options['id']))
		{
			$error[] = __('Die Benutzer-ID darf nicht leer sein!');
		}

		if (!empty($error))
		{
			add_settings_error('kumejo', 'kumejo', implode('<br />', $error));
		}

		ob_start(); ?>
		<div class="wrap">
			<h2><?php echo esc_html(get_admin_page_title()); ?></h2>
			<?php settings_errors(); ?>
			<form id="my-admin-form" method="post" action="options.php">
				<?php
				settings_fields('kumejo-settings');
				do_settings_sections('kumejo');
				submit_button();
				?>
			</form>
		</div>
		<?php
		ob_end_flush();
	}
}

if (!function_exists('kumejo_shortcode_replace'))
{
	function kumejo_shortcode_replace()
	{
		$postType = get_post()->post_type;

		if (is_admin() || kumejo_isset() || $postType == 'post')
		{
			return;
		}

		$error     = false;
		$options   = get_option('kumejo_options');

		if (empty($options['remote']))
		{
			$error = true;
		}

		if (empty($options['id']))
		{
			$error = true;
		}

		if ($error)
		{
			return;
		}

		$id  = $options['id'];
		$url = $options['remote'];
		$url .= '?tmpl=component';
		$url .= '#attr.author.value=' . $id;
		$url .= '&sort=' . $options['sort'];
		$url .= '&sortdir=' . $options['sortdir'];
		$url .= '&limiter=' . $options['limiter'];
		$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');

		/**
		 * Add dynamic css
		 */
		wp_register_style('kumejo', false);
		wp_enqueue_style('kumejo');
		wp_add_inline_style('kumejo',
			'iframe#kumejo-serp-' . $id . '{width: 1px;min-width: 100%;}'
		);

		/**
		 * Add dynamic js
		 */
		wp_register_script('kumejo', false);
		wp_enqueue_script('kumejo');
		wp_add_inline_script('kumejo', "
			function kumejoWPloadReady(fn) {
				if (document.readyState != 'loading') {
					fn();
				} else if (document.addEventListener) {
					document.addEventListener('DOMContentLoaded', fn);
				} else {
					document.attachEvent('onreadystatechange', function () {
						if (document.readyState != 'loading')
							fn();
					});
				}
			}

			function kumejoInit() {
				var test = iFrameResize({log: false}, '#kumejo-serp-" . $id . "');
				var el = document.querySelector('span.kumejo-loading');
				//console.log(test);
				//console.log('test !== undefined', typeof test !== undefined);
				//console.log('test.length > 0', test.length > 0);
				if (el !== undefined) {
					if (typeof test !== undefined && test.length > 0) {
						setTimeout(function () {
							el.parentNode.removeChild(el);
						}, 2000);
					} else {
						el.textContent = 'E R R O R';
					}
				}
			}

			kumejoWPloadReady(kumejoInit);
		");

		ob_start();
		?>
		<div id="kumejo-serp">
			<iframe id="kumejo-serp-<?php echo $id; ?>"
					allowfullscreen
					class="kumejo-serp"
					src="<?php echo $url; ?>"
					width="100%"
					scrolling="auto"
					frameborder="0"
			>
			</iframe>
			<span class="kumejo-loading">L o a d i n g . . .</span>
		</div>
		<?php
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}
}
add_shortcode('kumejo', 'kumejo_shortcode_replace');
