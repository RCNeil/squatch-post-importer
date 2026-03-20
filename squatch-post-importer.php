<?php
/*
Plugin Name: Squatch Post Importer
Plugin URI: https://squatchcreative.com
Description: Takes a CSV and turns them into posts
Version: 1.005
Author: Squatch Creative
Author URI: https://squatchcreative.com
*/

$plugin_data = get_file_data(__FILE__,array('Version' => 'Version'));
$plugin_version = $plugin_data['Version'];

define('SQUATCH_IMPORTER_PLUGIN', plugin_dir_url(__FILE__));
define('SQUATCH_IMPORTER_PATH', plugin_dir_path(__FILE__));

function squatch_post_importer_menu() {
	add_submenu_page(
		'tools.php',
		'Squatch Post Importer',
		'Squatch Post Importer',
		'manage_options',
		'squatch-post-importer',
		'squatch_post_importer_page'
	);
}
add_action('admin_menu', 'squatch_post_importer_menu');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'squatch_post_importer_settings_link');
function squatch_post_importer_settings_link($links) {
	$url = admin_url('tools.php?page=squatch-post-importer');
	$settings_link = '<a href="' . esc_url($url) . '">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}

add_filter('admin_footer_text', 'squatch_admin_footer_text');
function squatch_admin_footer_text_importer($footer_text) {
	$screen = get_current_screen();
	if ($screen && $screen->id === 'tools_page_squatch-post-importer') {
		$img_url = SQUATCH_IMPORTER_PLUGIN . 'assets/built-by-squatch.svg'; 
		ob_start();
		?>
		<span id="footer-thankyou">
			<a href="https://squatchcreative.com" title="Built By Squatch Creative" target="_blank">
				<img src="<?php echo esc_url($img_url); ?>" alt="Built By Squatch Creative">
			</a>
		</span>
		<?php
		return ob_get_clean();
	}

	return $footer_text;
}








add_action('admin_head', function() {
	$screen = get_current_screen();
	if($screen && $screen->id === 'tools_page_squatch-post-importer') {
		?>
		<style>
		.squatch-plugin-header {
			display: flex;
			gap: 18px;
			align-items: center;
			padding: 18px 0;
		}
		.squatch-plugin-header img {
			display: block;
			margin: 0;
			width: 54px;
			height: 54px;
			background: black;
			border-radius: 50%;
			padding: 2px;
		}
		.squatch-header-text * {
			margin: 0 !important;
			padding: 0 !important;
		}
		#squatch-plugin-progress {
			margin: 12px 0;
			background: #eee;
			border: 1px solid #ccc;
			height: 20px;
			width: 100%;
			position: relative;
			border-radius: 6px;
			overflow: hidden;
		}
		#squatch-plugin-bar {
			background: #0073aa;
			width: 0%;
			height: 100%;
			transition: width 0.3s ease;
		}
		#squatch-plugin-output {
			overflow: auto;
			max-height: 400px;
			background: #1d2327;
			padding: 18px;
			border-radius: 8px;
			color: white;
		}
		#squatch-plugin-output a {
			color: white;
			text-decoration: none;
		}
		#squatch-plugin-output strong {
			display:inline-block;
			color: #ffd747;
		}
		#squatch-plugin-summary {
			padding: 20px 0 48px 0;
			font-size: 16px;
			font-weight: bold;
		}
		#post_importer_form {
			transition: 180ms ease all;
			position: relative;
			display: flex;
			gap: 12px;
			flex-flow: column;
			align-items: flex-start;
		}
		#post_importer_form.processing {
			opacity: 0.6;
			pointer-events: none;
		}
		#post_importer_form.processing button {
			cursor: not-allowed;
		}
		#post_importer_form.processing::after {
			content: "\f463";
			font-family: dashicons;
			display: block;
			font-size: 24px;
			animation: SyncSpin 1s linear infinite;
		}
		@keyframes SyncSpin {
			from { transform: rotate(0deg);	}
			to { transform: rotate(360deg);	}
		}
		#post_importer_form label {
			display: block;
			min-width: 100px;
		}
		.form-field {
			display: flex;
			gap: 18px;
			width: 480px;
			max-width: 100%;
		}

		.form-field input, .form-field select {
			display: block;
			flex: 1;
		}
		</style>
		<?php
	}
});

function squatch_post_importer_page() {
	
	wp_enqueue_media();
	
	$img_url = SQUATCH_IMPORTER_PLUGIN . 'assets/squatch-mark-yellow.svg'; 
	$post_types = get_post_types(array(
		'public' => true
	), 'objects');
	
	echo '<div class="wrap">';
	echo '<div class="squatch-plugin-header"><img src="' . esc_url($img_url). '" alt="Built By Squatch Creative"><div class="squatch-header-text"><h1>Squatch Post Importer</h1><p>Used in conjunction with <a href="https://github.com/RCNeil/squatch-post-exporter" target="_blank">Squatch Post Exporter</a>. Imports your posts from a CSV. <a href="https://github.com/RCNeil/squatch-post-importer" target="_blank">View Details</a></p></div></div>';
	echo '<form id="post_importer_form">';
	echo '<div class="form-field">
		<label><strong>CSV File</strong></label>
		<input type="text" id="csv_file" name="csv_file" class="regular-text" readonly>
		<button type="button" class="button" id="select_csv">Select CSV</button>
	</div>';
	echo '<div class="form-field"><label for="old_url"><strong>Old URL Base</strong></label><input type="text" id="old_url" name="old_url" class="regular-text" placeholder="https://oldsite.com"></div>';
	echo '<div class="form-field"><label for="new_url"><strong>New URL Base</strong></label><input type="text" id="new_url" name="new_url" class="regular-text" value="' . esc_attr(site_url()) . '"></div>';
	echo '<div class="form-field"><label for="post_type"><strong>Set Post Type</strong></label><select id="post_type" name="post_type">';
		foreach ($post_types as $pt) {
			echo '<option value="' . esc_attr($pt->name) . '">' . esc_html($pt->label) . '</option>';
		}
	echo '	</select></div>';
	echo '<input type="hidden" name="_nonce" value="' . esc_attr(wp_create_nonce('post_import_nonce')) . '">';
	echo '<button type="submit" class="button button-primary">Start Sync</button>';
	echo '</form>';
	echo '<div id="squatch-plugin-progress"><div id="squatch-plugin-bar"></div></div>';
	echo '<div id="squatch-plugin-output"></div>';
	echo '<div id="squatch-plugin-summary"></div>';
	echo '</div>';
	?>
	<script>
	jQuery(document).ready(function($) {
		var $form = $('#post_importer_form');
		var $outputDiv = $('#squatch-plugin-output');
		var $progressBar = $('#squatch-plugin-bar');
		var $summaryDiv = $('#squatch-plugin-summary');
		var file_frame;
		
		$('#select_csv').on('click', function(e) {
			e.preventDefault();
			if(file_frame) {
				file_frame.open();
				return;
			}
			file_frame = wp.media({
				title: 'Select CSV File',
				button: { text: 'Use this file' },
				multiple: false,
				library: {
					type: 'text/csv'
				}
			});
			file_frame.on('select', function() {
				var attachment = file_frame.state().get('selection').first().toJSON();
				$('#csv_file').val(attachment.url);
			});
			file_frame.open();
		});

		$form.on('submit', function(e) {
			e.preventDefault();

			$form.addClass('processing');
			$outputDiv.html('');
			$summaryDiv.html('');
			$progressBar.css('width', '0%');

			var start = 0;
			
			var oldUrl  = $('#old_url').val();
			var newUrl  = $('#new_url').val();
			var postType = $('#post_type').val();
			var csvFile = $('#csv_file').val();

			function processBatch() {
				$.ajax({
					url: ajaxurl,
					method: 'POST',
					dataType: 'json',
					data: {
						action: 'squatch_import_posts',
						start: start,
						csv_file: csvFile,
						old_url: oldUrl,
						new_url: newUrl,
						post_type: postType,
						_nonce: '<?php echo wp_create_nonce("post_import_nonce"); ?>'
					},
					success: function(res) {
						if (res.success) {
							$outputDiv.append(res.data.output);
							$outputDiv.scrollTop($($outputDiv)[0].scrollHeight);
							var percent = Math.min(100, Math.round((res.data.next_start / res.data.total) * 100));
							$progressBar.css('width', percent + '%');
							if (!res.data.done) {
								start = res.data.next_start;
								processBatch();
							} else {
								$summaryDiv.html('<strong>Import complete!</strong>');
								$form.removeClass('processing');
							}
						} else {
							alert(res.data.message || 'Error during import');
							$form.removeClass('processing');
						}
					},
					error: function() {
						alert('AJAX request failed');
						$form.removeClass('processing');
					}
				});
			}
			processBatch();
		});
	});
	</script>
	<?php
}











add_action('wp_ajax_squatch_import_posts', function() {

	global $wpdb;
	
	check_ajax_referer('post_export_nonce', '_nonce');

	//$test_limit = 40;
	$test_limit = null;

	$batch_size = 3;
	$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
	
	$old_url  = isset($_POST['old_url']) ? sanitize_text_field($_POST['old_url']) : '';
	$new_url  = isset($_POST['new_url']) ? sanitize_text_field($_POST['new_url']) : '';
	$new_post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
	
	$csv_file_URL = isset($_POST['csv_file']) ? esc_url_raw($_POST['csv_file']) : '';
	if(empty($csv_file_URL)) {
		wp_send_json_error(['message' => 'No CSV file provided']);
	}
	$csv_file = str_replace(site_url('/'), ABSPATH, $csv_file_URL);
	if (!file_exists($csv_file)) {
		wp_send_json_error(['message' => 'CSV file not found']);
	}
	
	$rows = [];
	$headers = [];

	if (($handle = fopen($csv_file, 'r')) !== false) {
		$headers = fgetcsv($handle);
		while (($data = fgetcsv($handle)) !== false) {
			$rows[] = array_combine($headers, $data);
		}
		fclose($handle);
	}
	
	$total_posts = count($rows);

	if ($start >= $total_posts) {
		wp_send_json_success([
			'output' => '',
			'total' => $total_posts,
			'next_start' => $total_posts,
			'done' => true
		]);
	}

	$output = '';

	$limit = ($test_limit !== null) ? min($test_limit, $total_posts) : $total_posts;
	$end = min($start + $batch_size, $limit);

	for ($i = $start; $i < $end; $i++) {

		$row = $rows[$i];

		$title = $row['Title'] ?? '';
		$slug = $row['Slug'] ?? '';
		$status = $row['Post Status'] ?? 'publish';
		$author_login = $row['Author Username'] ?? '';
		$author_email = $row['Author Email'] ?? '';
		$date_raw = $row['Publish Date'] ?? '';
		$content = $row['Content'] ?? '';

		$user = get_user_by('login', $author_login);
		if (!$user && is_email($author_email)) {
			$user = get_user_by('email', $author_email);
		}
		$author_id = $user ? $user->ID : get_current_user_id();
		$post_date = date('Y-m-d H:i:s', strtotime($date_raw));

		if (!empty($content) && !empty($old_url) && !empty($new_url)) {
			$content = str_replace($old_url, $new_url, $content);
		}
		
		$post_data = [
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => $status ?: 'publish',
			'post_date'     => $post_date,
			'post_date_gmt' => get_gmt_from_date($post_date),
			'post_type'     => $new_post_type,
			'post_name'     => $slug,
			'post_author'   => $author_id
		];
		
		//CHECK FOR EXISTING
		/*
		$existing_slug = get_page_by_path($slug, OBJECT, $new_post_type);
		$existing_title = get_page_by_title($title, OBJECT, $new_post_type);
		if ($existing_slug || $existing_title) {
			$output .= '<strong>SKIPPED (exists):</strong> ' . esc_html($title) . '<br /><br />';
			continue;
		}
		*/
		

		$post_id = wp_insert_post($post_data);
		if (is_wp_error($post_id) || !$post_id) {
			$output .= '<strong>FAILED:</strong> ' . esc_html($title) . '<br /><br />';
			continue;
		}
		
		//SET TAXONOMIES
		/*
		$term_slug = 'your-slug';
		$taxonomy_name = 'your_taxonomy';
		wp_set_object_terms($post_id, $term_slug, $taxonomy_name, false);
		*/
			
		//FEATURED IMAGE		
		$image_url = $row['Featured Image URL'] ?? '';
		$featured = find_featured_image($image_url);
		if ($featured['id']) {
			set_post_thumbnail($post_id, $featured['id']);
		}		
		
		
		
		// MAP YOUR YOAST SEO (OR OTHER SOURCE) HERE
		$seo = false; 
		$yoast_map = [];
		/*
		$yoast_map = [
			'_yoast_wpseo_title'      			  => '_yoast_wpseo_title',
			'_yoast_wpseo_metadesc'				  => '_yoast_wpseo_metadesc',
			'_yoast_wpseo_focusk'   			  => '_yoast_wpseo_focuskw',
			'_yoast_wpseo_canonical' 			  => '_yoast_wpseo_canonical',
			'_yoast_wpseo_meta-robots-noindex'    => '_yoast_wpseo_meta-robots-noindex',
			'_yoast_wpseo_meta-robots-nofollow'   => '_yoast_wpseo_meta-robots-nofollow',
		];
		*/
		
		foreach ($yoast_map as $csv_header => $meta_key) {
			if (!empty($row[$csv_header])) {
				$value = $row[$csv_header];

				// For focus keyword, only take the first if multiple are provided
				if ($csv_header === '_yoast_wpseo_focusk') {
					$keywords = explode(',', $value);
					$value = trim($keywords[0]);
				}
				update_post_meta($post_id, $meta_key, $value);
				$seo = true;
			}
		}
		
		
		
		// MAP ANY META YOU WANT TO MAP HERE (ACF,CUSTOM FIELDS, ETC)
		$custom_mapping = false;
		$custom_map = []; 		
		/*		
		$custom_map = [
			'custom_field_1'      => 'custom_field_1',
			'custom_field_2'      => 'custom_field_2',
			'custom_field_3'      => 'custom_field_3',
			'custom_field_4'      => 'custom_field_4',
			'custom_field_5'      => 'custom_field_5',
		];		
		*/
				
		foreach ($custom_map as $csv_header => $meta_key) {
			if (!empty($row[$csv_header])) {
				$value = $row[$csv_header];
				update_post_meta($post_id, $meta_key, $value);
				$custom_mapping = true;
			}
		}	
		
		
		
		$permalink = get_permalink($post_id);
		$output .= '#' . $i . ' <strong> ' . esc_html($title) . '</strong> ';
		$output .= 'ID: ' . $post_id . '<br>';
		$output .= '<a href="' . esc_url($permalink) . '" target="_blank">' . esc_html($permalink) . '</a><br />';
		$output .= '<strong>Author:</strong> ' . esc_html($author_login) . ' (ID ' . $author_id . ') &bull; ';
		$output .= '<strong>Date:</strong> ' . esc_html($post_date) . '<br>';
		if($featured['id']) { $output .= '&bull; <strong>Featured Image:</strong> ' . esc_html($featured['filename']) . ' was attached with ID ' . intval($featured['id']); }
		if ($seo) {	$output .= ' &bull; <strong>SEO UPDATED</strong>'; }
		if ($custom_mapping) {	$output .= ' &bull; <strong>POST META MAPPED</strong>'; }
		$output .= '<br /><br />';
	}

	$next_start = $end;
	$done = $next_start >= $limit;

	wp_send_json_success([
		'output' => $output,
		'total' => $total_posts,
		'next_start' => $next_start,
		'done' => $done
	]);

	wp_die();

});

function find_featured_image($image_url) {
	if(empty($image_url)) {
		return ['id' => false, 'filename' => ''];
	}
	global $wpdb;

	// Get filename from URL
	$filename = basename(parse_url($image_url, PHP_URL_PATH));

	// Remove WP dimension suffix (-768x245 etc.)
	$filename = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp)$)/i', '', $filename);

	// Search uploads for matching file
	$attachment_id = $wpdb->get_var($wpdb->prepare("
		SELECT ID
		FROM {$wpdb->posts}
		WHERE post_type = 'attachment'
		AND guid LIKE %s
		LIMIT 1
	", '%' . $wpdb->esc_like($filename)));

	return [
		'id' => $attachment_id ? intval($attachment_id) : false,
		'filename' => $clean_filename
	];
}
