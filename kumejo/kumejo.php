<?php
/*
 * Plugin Name: Kumejo SERP
 * Plugin URI: https://www.kunze-medien.de
 * Description: This Plugin implements kumejo.de SERP in an iFrame.
 * Version: 1.0
 * Author: Guido De Gobbis
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
if ( ! defined( 'WPINC' ) ) {
	die;
}

function kumejo() {
	$id  = '941';
	$url = 'http://localhost/kumejo/jobsuche.html';
	$url .= '?tmpl=component';
	$url .= '#attr.author.value=' . $id;
	$url .= '&sort=name';
	$url .= '&sortdir=desc';
	$url .= '&limiter=4';
	$url = htmlspecialchars( $url, ENT_COMPAT, 'UTF-8' );

	$scriptPath = plugins_url('js/iframeResizer.min.js', __FILE__);
	?>
	<script src="<?php echo $scriptPath; ?>" async></script>

	<style>
		iframe#kumejo-serp-<?php echo $id; ?> {
			width: 1px;
			min-width: 100%;
		}
	</style>
	<iframe id="kumejo-serp-<?php echo $id; ?>"
			allowfullscreen
			class="kumejo-serp"
			src="<?php echo $url; ?>"
			width="100%"
			scrolling="auto"
			frameborder="0"
	>
	</iframe>
	<script type="text/javascript">

		function kumejoWPloadReady(fn) {
			if (document.readyState != 'loading'){
				fn();
			} else if (document.addEventListener) {
				document.addEventListener('DOMContentLoaded', fn);
			} else {
				document.attachEvent('onreadystatechange', function() {
					if (document.readyState != 'loading')
						fn();
				});
			}
		}

		function kumejoInit()
		{
			iFrameResize({log: false}, '#kumejo-serp-<?php echo $id; ?>')
		}

		kumejoWPloadReady(kumejoInit);
	</script>
	<?php
}

add_shortcode( 'kumejo', 'kumejo' );
