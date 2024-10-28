<?php
/**
 *	Administration menus for Aceti SEO
 *
 *	Adds Settings submenu page for installation wizard
 *	and settings page thereafter.
 */


/**
 *	Check if setup program needs to run. If so, it will
 *	deliver the setup page and collect initial data. If not
 *	it will deliver the maintenance page for changing options.
 */
function ac_admin_options() {

	global $ac_settings;
	
	if ( $ac_settings->do_setup ) { 
	
		$slug = add_options_page( 'Aceti Dynamic SEO', 'Aceti Dynamic SEO', 'manage_options', 'ac-manage-page', 'ac_setup_page' );
		add_action('admin_print_styles-' . $slug, 'sp_enq_script');
		
		if ( $_GET['page'] != 'ac-manage-page' ) {
		
			$notice_str = admin_url() . 'options-general.php?page=ac-manage-page';
			ac_notice ( __('Please perform the <a href="' . $notice_str . '">Aceti Dynamic SEO setup</a>.') );
			
		}
		
	} else {
	
		$slug = add_options_page( 'Aceti Dynamic SEO', 'Aceti Dynamic SEO', 'manage_options', 'ac-manage-page', 'ac_manage_page' );
		add_action('admin_print_styles-' . $slug, 'sp_enq_script');
		
	}
		
}
add_action( 'admin_menu', 'ac_admin_options' );

// Enqueue scripts 
function sp_enq_script () {

	wp_enqueue_script('aceti-script');
	wp_enqueue_style('aceti-style');
	
}

// Register Scripts
function sp_reg_script () {

	wp_register_style( 'aceti-style', plugins_url('aceti-style.css', __FILE__) );
	wp_register_script( 'aceti-script', plugins_url('aceti-script.js', __FILE__), array('jquery','jquery-ui-draggable'), '1.0', true );
	
}
add_action( 'init', 'sp_reg_script' );

/**
 * -------------------------------------------------------
 * MARKUP FOR SET UP PAGE
 * -------------------------------------------------------
 * Gather initial data to enable the plugin.
 */
function ac_setup_page () {

	if ( !current_user_can('manage_options') )
		  wp_die( __('You do not have sufficient permissions to access this page.') );
	
	global $ac_settings;

	if ( isset($_POST['aceti_title']) ) {
	
		if ( !check_admin_referer( 'sp_nonce', 'sp_form' ) ) return false;
		
		$ac_settings->is_front_page->title = htmlspecialchars(stripslashes($_POST['aceti_title']));
		$ac_settings->is_front_page->description = htmlspecialchars(stripslashes($_POST['aceti_description']));
		$ac_settings->is_front_page->keywords = htmlspecialchars(stripslashes($_POST['aceti_keywords']));
		$ac_settings->do_setup = 0;
		$ac_settings->activated = 1;

		ac_save_settings( $ac_settings );
	
		ac_manage_page();
		return true;
	
	}

	
	

	?>
	
<div class="wrap">
	
	<h2>Aceti Dynamic SEO</h2>
	
	<h3><?php _e( 'Thank you for choosing Aceti Dynamic SEO!' ); ?></h3>
	
	<h3><?php _e('Home Settings'); ?></h3>
	<form action="" method="POST" />
	<?php wp_nonce_field( 'sp_nonce', 'sp_form' ) ?>
	<div class="seo-inner-form">
		<div>
			<h4><?php _e('Title'); ?></h4>
			<p><?php _e("This is how your home page title will display. This will be displayed in your browser's title bar and be used by search engines as the main title of your site. <span class=\"sp-highlight\">%blogname%</span> - The name of your site."); ?></p>
			<input type="text" class="is_front_page_title" name="aceti_title" value="<?php echo $ac_settings->is_front_page->title; ?>" />
		</div>
		<div>
			<h4><?php _e('Description'); ?></h4>
			<p><?php _e('This is the description of your site. It will often be used by search engines to display information about your site under the title. An accurate, short (1 or 2 sentences) will be appropriate here.'); ?></p>
			<textarea class="is_front_page_description" name="aceti_description"><?php echo $ac_settings->is_front_page->description; ?></textarea>
		</div>
		<div>
			<h4><?php _e('Keywords'); ?></h4>
			<p><?php _e('These are keywords. Just set a few of the most relevant terms for your site. Search engines are likely to ignore these, and penalize you if you use too many keywords, but you can set them here. Separate by comma.'); ?></p>
			<input class="is_front_page_keywords" type="text" name="aceti_keywords" value="<?php echo $ac_settings->is_front_page->keywords; ?>" />
		</div>
		<div>
			<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
		</div>
	</form>
	</div>
	
</div>
	<?php
}

/**
 * -------------------------------------------------------
 * MARKUP FOR MANAGEMENT PAGE
 * -------------------------------------------------------
 *
 * Get settings data for plugin
 *
 */
function ac_manage_page () {


	if ( !current_user_can('manage_options') )
		  wp_die( __('You do not have sufficient permissions to access this page.') );
		  
	if ( isset($_POST['sp_email']) )
		sp_tech_support();

	global $ac_settings;

	?>
	
<div class="wrap">

<div id="SEO-wrapper">

	<div id="SEO-header">
		<h2>Aceti Dynamic SEO</h2>

		<p><?php _e('<strong>Welcome to Aceti SEO!</strong> Manage settings + support options. Click input boxes for explanations.'); ?></p>
		
		<div id="on_off"><img style="float:<?php echo ( $ac_settings->activated ? 'left' : 'right' ); ?>;" src="<?php echo plugins_url( 'aceti.png' , __FILE__ ); ?>" id="on_switch" /></div>
	</div>
	
	<div id="SEO-leftmenu">
		<ul>
			<li><a href="#home_settings" class="seo_a seo-active"><?php _e('Home Settings' ); ?></a></li>
			<li><a href="#title_format" class="seo_a"><?php _e('Title Formats' ); ?></a></li>
			<li><a href="#meta_tags" class="seo_a"><?php _e('Meta Tags' ); ?></a></li>
			<li><a href="#content" class="seo_a"><?php _e('Content' ); ?></a></li>
			<li><a href="#misc" class="seo_a"><?php _e('Misc. Settings' ); ?></a></li>
			<li><a href="#support" class="seo_a"><?php _e('Tech Support' ); ?></a></li>
		</ul>
	</div>
	
	<div id="SEO-content">
		<form action="" method="POST" id="ac_settings">
		<?php wp_nonce_field( 'sp_nonce', 'sp_form' ) ?> 
		<div id="home_settings" class="seo-panel">
			<h3><?php _e('Home Settings'); ?></h3>
			<div class="seo-inner-form">
				<div>
					<h4><?php _e('Title'); ?></h4>
					<input type="text" class="is_front_page_title" name="ac_settings[is_front_page][title]" value="<?php echo $ac_settings->is_front_page->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Description'); ?></h4>
					<textarea class="is_front_page_description" name="ac_settings[is_front_page][description]"><?php echo $ac_settings->is_front_page->description; ?></textarea>
				</div>
				<div>
					<h4><?php _e('Keywords'); ?></h4>
					<input class="is_front_page_keywords" type="text" name="ac_settings[is_front_page][keywords]" value="<?php echo $ac_settings->is_front_page->keywords; ?>" />
				</div>
				<div>
					<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
					<img src="<?php echo admin_url( '/images/wpspin_light.gif' );?>" class="sp_spinner" />
				</div>
			</div>
			
			<div class="seo-helper">
				<div id="is_front_page_title" style="display:block;<?php echo ($_GET['page'] == 'ac_manage_page' ? 'position:static;' : ''); ?>">
					<h5><?php _e('Home Title'); ?></h5>
					<p><?php _e('Your title will display on the user\'s browser, typically on the name of the window. This is often used by search engines as the heading link to the home page of your website.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_title%></span> - <?php _e('The title of the static page used for your site, if set.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_front_page_description">
					<h5><?php _e('Home Description'); ?></h5>
					<p><?php _e('This is a meta tag that search engines will most likely use to display information about the home page to your website.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_title%</span> - <?php _e('The title of the static page used for your site, if set.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_front_page_keywords">
					<h5><?php _e('Home Keywords'); ?></h5>
					<p><?php _e('Keywords do not have much bearing on your search engine performance, and in fact can have a negative impact if you use too many and/or irrelevant keywords. Be careful to select only a few of the most relevant words, separated by a comma.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_title%</span> - <?php _e('The title of the static page used for your site, if set.'); ?></li>
						</ul>
					</p>
				</div>
			</div>
		
		</div>
		<div id="title_format" class="seo-panel">
			<h3><?php _e('Title Formats'); ?></h3>
			
			<div class="seo-inner-form">
				<div>
					<h4><?php _e('Single Post Title'); ?></h4>
					<input type="text" class="is_single_title" name="ac_settings[is_single][title]" value="<?php echo $ac_settings->is_single->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Page Title'); ?></h4>
					<input type="text" class="is_page_title" name="ac_settings[is_page][title]" value="<?php echo $ac_settings->is_page->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Archive Page Title'); ?></h4>
					<input type="text" class="is_archive_title" name="ac_settings[is_archive][title]" value="<?php echo $ac_settings->is_archive->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Author Profile Page Title'); ?></h4>
					<input type="text" class="is_author_title" name="ac_settings[is_author][title]" value="<?php echo $ac_settings->is_author->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Category Page Title'); ?></h4>
					<input type="text" class="is_category" name="ac_settings[is_category][title]" value="<?php echo $ac_settings->is_category->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Tag Page Title'); ?></h4>
					<input type="text" class="is_tag" name="ac_settings[is_tag][title]" value="<?php echo $ac_settings->is_tag->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Taxonomy Page Title'); ?></h4>
					<input type="text" class="is_tax" name="ac_settings[is_tax][title]" value="<?php echo $ac_settings->is_tax->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Search Page Title'); ?></h4>
					<input type="text" class="is_search" name="ac_settings[is_search][title]" value="<?php echo $ac_settings->is_search->title; ?>" />
				</div>
				<div>
					<h4><?php _e('404 Page Title'); ?></h4>
					<input type="text" class="is_404" name="ac_settings[is_404][title]" value="<?php echo $ac_settings->is_404->title; ?>" />
				</div>
				<div>
					<h4><?php _e('Pagination Page Title'); ?></h4>
					<input type="text" class="is_paged" name="ac_settings[is_paged][title]" value="<?php echo $ac_settings->is_paged->title; ?>" />
				</div>
				<div>
					<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
					<img src="<?php echo admin_url( '/images/wpspin_light.gif' );?>" class="sp_spinner" />
					
					<input type="checkbox" name="disable_titles" value="1" <?php echo ( $ac_settings->title_rewrite ? '' : 'checked="checked"'); ?> /> Disable title re-writing?
				</div>
				
				<div class="seo-helper">
				<div id="is_single_title" style="display:block;">
					<h5><?php _e('Single Post Title'); ?></h5>
					<p><?php _e('This will be the title of a single post; visible to the search engine and in the reader\'s browser window.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_title%</span> - <?php _e('The title of the static page used for your site, if set.'); ?></li>
							<li><span class="sp-highlight">%date%</span> - <?php _e('The date of the post, formatted in Settings > General.'); ?></li>
							<li><span class="sp-highlight">%post_category%</span> - <?php _e('The category of the post.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_page_title">
					<h5><?php _e('Page Title'); ?></h5>
					<p><?php _e('Title format for pages; visible to the search engine and in the reader\'s browser window.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_title%</span> - <?php _e('The title of the static page used for your site, if set.'); ?></li>
							<li><span class="sp-highlight">%date%</span> - <?php _e('The date of the page, formatted in Settings > General.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_archive_title">
					<h5><?php _e('Archive Title'); ?></h5>
					<p><?php _e('The index to your posts archive. This is visible to users, and search engines only if you allow indexing on the archive.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%date%</span> - <?php _e('The date of the archive, formatted in Settings > General.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_author_title">
					<h5><?php _e('Author Title'); ?></h5>
					<p><?php _e('The page displaying information (name, bio, posts written, etc.) about individual authors. Visible to search engines and users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%post_author_nicename%</span> - <?php _e('Display name of the author.'); ?></li>
							<li><span class="sp-highlight">%post_author_firstname%</span> - <?php _e('First name of author.'); ?></li>
							<li><span class="sp-highlight">%post_author_lastname%</span> - <?php _e('Last name of author.'); ?></li>
							<li><span class="sp-highlight">%post_author_login%</span> - <?php _e('Username for login of author (give consideration to this one.)'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_time">
					<h5><?php _e('Date/Time Page Title'); ?></h5>
					<p><?php _e('The page displaying content for a specific date or time. Visible to search engines and users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site'); ?></li>
							<li><span class="sp-highlight">%datetime%</span> - <?php _e('Display the date for the query.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_category">
					<h5><?php _e('Category Page Title'); ?></h5>
					<p><?php _e('The page displaying posts for a category. Visible to search engines, if selected, and to users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%category_title%</span> - <?php _e('The title of the category index.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_tag">
					<h5><?php _e('Tag Page Title'); ?></h5>
					<p><?php _e('Similar to a category page, displaying posts according to the tag selected. Visible to search engines, if selected, and to users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%tag_title%</span> - <?php _e('The title of the category index.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_tax">
					<h5><?php _e('Taxonomy Page Title'); ?></h5>
					<p><?php _e('Similar to a category page, displaying posts according to the taxonomy selected. Visible to search engines, if selected, and to users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%tax_title%</span> - <?php _e('The title of the category index.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_search">
					<h5><?php _e('Search Page Title'); ?></h5>
					<p><?php _e('Search page title. Displays to user--search engines will not typically reach this page.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%search%</span> - <?php _e('The terms used in the search query.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_404">
					<h5><?php _e('404 Page Title'); ?></h5>
					<p><?php _e('404 Not Found page title. This will be visible to users.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%request_url%</span> - <?php _e('The URL requested.'); ?></li>
							<li><span class="sp-highlight">%request_words%</span> - <?php _e('The URL requested in ordinary language terms.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="is_paged">
					<h5><?php _e('Paginated Page Title'); ?></h5>
					<p><?php _e('Content that spans more than one page, such as your blog index, starting at page 2, will show this title.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
							<li><span class="sp-highlight">%page%</span> - <?php _e('Page number requested.'); ?></li>
						</ul>
					</p>
				</div>
			</div>
			</div>
		
		</div>
		<div id="meta_tags" class="seo-panel">
			<h3><?php _e('Meta Tags'); ?></h3>
			
			<div class="seo-inner-form">
				<div>
					<h4><?php _e('Keyword Generation'); ?></h4>
					<?php 
					
					$preptax = '<input %s type="checkbox" name="create_keywords[%s]" value="1" /> Use %s <br />';
					$taxes = get_taxonomies(array('public'   => true,'_builtin' => true),'objects');
					
					foreach ( $taxes as $tax => $term ) {
						
						if ( $tax == 'post_format' ) break;
						
						$first = ( in_array( $tax, $ac_settings->create_keywords ) ? 'checked="checked"' : '' );
						$second = $tax;
						$third = $term->labels->name;
						
						printf($preptax, $first, $second, $third);
						
					}
					
					?>

				</div>
				<div>
					<h4><?php _e('Description Generation'); ?></h4>
					<input class="descripthelp" type="text" name="ac_settings[description]" value="<?php echo $ac_settings->description; ?>" />
				</div>
				<div>
					<h4><?php _e('Universal Keywords'); ?></h4>
					<input class="ukeywords" type="text" name="ac_settings[append_keywords]" value="<?php echo $ac_settings->append_keywords; ?>" />
				</div>
				<div>
					<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
					<img src="<?php echo admin_url( '/images/wpspin_light.gif' );?>" class="sp_spinner" />
				</div>
				<div class="seo-helper">
				<div style="display:block;" id="metahelper">
					<h5><?php _e('Keyword Generation'); ?></h5>
					<p><?php _e('You will have a chance to manually set keywords for each of your posts. If you do not, Aceti SEO can generate keywords for you. You must check which of these sources you want to generate keywords.'); ?></p>

				</div>
				<div id="descripthelp">
				<h5><?php _e('Description Generation'); ?></h5>
					<p><?php _e('You will have a chance to manually set description for each of your posts. If you do not, Aceti SEO can automatically create a description and save it for your post.'); ?></p>
					<p><?php _e('Allowed tags:'); ?></p>
					<p>
						<ul>
							<li><span class="sp-highlight">%description%</span> - <?php _e('This will be the description you set when writing your post, or as generated by Aceti SEO.'); ?></li>
							<li><span class="sp-highlight">%blogname%</span> - <?php _e('The name of your site.'); ?></li>
						</ul>
					</p>
				</div>
				<div id="ukeywords">
				<h5><?php _e('Universal Keywords'); ?></h5>
					<p><?php _e('Here you can add keywords you\'d like added to any keywords list other than for your home page/main index.'); ?></p>
				</div>
				</div>
			</div>
		
		</div>
		<div id="content" class="seo-panel">
			<h3><?php _e('Content'); ?></h3>
			
			<div class="seo-inner-form">
				<div>
					<h4><?php _e('Canonical URLs'); ?></h4>
					<input type="checkbox" name="canonical" value="1" <?php echo ( $ac_settings->canonical ? 'checked="checked"' : ''); ?> /> <?php _e('Yes, use canonical tags'); ?>.
				</div>
				<div>
					<h4>Noindex These:</h4>
					<input type="checkbox" name="noindex[is_category]" value="1" <?php echo ( in_array('is_category', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Category Pages'); ?> <br />
					<input type="checkbox" name="noindex[is_archive]" value="1" <?php echo ( in_array('is_archive', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Archive Pages'); ?> <br />
					<input type="checkbox" name="noindex[is_tag]" value="1" <?php echo ( in_array('is_tag', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Tag Pages'); ?> <br />
					<input type="checkbox" name="noindex[is_tax]" value="1" <?php echo ( in_array('is_tax', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Taxonomy Pages'); ?> <br />
					<input type="checkbox" name="noindex[is_feed]" value="1" <?php echo ( in_array('is_feed', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Feed'); ?> <br />
					<input type="checkbox" name="noindex[is_comment_feed]" value="1" <?php echo ( in_array('is_comment_feed', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Comment Feed'); ?> <br />
					<input type="checkbox" name="noindex[is_search]" value="1" <?php echo ( in_array('is_search', $ac_settings->no_index) ? 'checked="checked"' : ''); ?> /> <?php _e('Search'); ?> <br />
				</div>
				<div>
					<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" />
					<img src="<?php echo admin_url( '/images/wpspin_light.gif' );?>" class="sp_spinner" />
				</div>
			</div>
			<div class="seo-helper">
				<div style="display:block;">
					<h5><?php _e('Canonical URLs'); ?></h5>
					<p><?php _e('Canonical tags help manage duplicate content. For example, one of your posts might be found on several different URLs. When encountered by a search engine crawler, it will index each instance unless one link is named to be THE location of the post.'); ?></p>
					
					<h5><?php _e('Noindex'); ?></h5>
					<p><?php _e('Your content might display to used on several indexes, including archive, category, tag, or other taxonomy pages. By using noindex, you steer search engines away from duplicate indexing.'); ?></p>
				</div>
			</div>
		</div>
		<div id="misc" class="seo-panel">
			<h3><?php _e('Misc. Settings'); ?></h3>
			
			<div class="seo-inner-form">
				<div>
					<h4><?php _e('Exclude Segments'); ?></h4>
					<p><input type="text" class="exclude_segment" name="expl_settings[exclude_segment]" value="<?php print_r(implode(', ',$ac_settings->exclude_segment)); ?>" /></p>
				</div>
				<div>
					<h4><?php _e('Specific Exclude List'); ?></h4>
					<p><input type="text" class="exclude" name="expl_settings[exclude]" value="<?php print_r(implode(', ',$ac_settings->exclude)); ?>" /></p>
				</div>
				<div>
					<h4><?php _e('Capitalize Titles'); ?></h4>
					<p><input type="checkbox" name="capitalize" <?php echo ( $ac_settings->capitalize ? 'checked="checked"' : '' ); ?> value="1" /> Yes, please capitalize titles.</p>
				</div>
				<div>
					<h4><?php _e('Enhance Performance <a href="#by_hook" class="get_byhook">(?)</a>'); ?></h4>
					<p><input type="checkbox" name="by_hook" value="1" <?php echo ( $ac_settings->by_hook ? 'checked="checked"' : '' ); ?> /> Yes, use healthy Wordpress API standards (so we all can sleep better at night.)</p>
				</div>
				<div>
					<input type="submit" value="<?php _e('Save Settings'); ?>" class="button-primary" /> 
					<img src="<?php echo admin_url( '/images/wpspin_light.gif' );?>" class="sp_spinner" />
				</div>
			</div>
			<div class="seo-helper">
				<div id="exclude_segment" style="display:block;">
					<h5><?php _e('Exlcude Segments'); ?></h5>
					<p><?php _e('If your URL contains any of these words, it will not get Aceti SEO support. This is good for some themes and plugins that need to manage your titles and keywords in other ways. Separate with comma.'); ?></p>
				</div>
				<div id="exclude">
					<h5><?php _e('Exlcude List'); ?></h5>
					<p><?php _e('Enter the name of conditions or post IDs that you\'d like to exclude from Aceti SEO support. Separate with comma.'); ?></p>
				</div>
				<div id="by_hook">
					<h5><?php _e('Hook vs Output Buffering'); ?></h5>
					<p><?php _e('By default, you are set to use something called Output Buffering. Before output, like HTML, is sent to users Aceti SEO runs the entire page through a regular expression checker to replace your title tag. While this is really easy to use, applying a regular expression to the full output buffering limit can have a large performance impact for such a tiny payoff. Furthermore, it may cause conflicts with other plugins or themes that use output buffering.'); ?></p>
					<p><?php _e('To gain best performance and to avoid conflicts, it is strongly recommended you use hook-only methods. To do this, check the box and make sure your header.php file(s) contain the template tag <span class="sp-highlight">wp_head();</span> or, if you do not like to load the entire <span class="sp-highlight">wp_head();</span> and just want the SEO parts, use this instead: <span class="sp-highlight">ac_head();</span>'); ?></p>
					<p><?php _e('Finally, in the header.php file(s), just remove or comment-out the &lt;title&gt;&lt;/title&gt; HTML tags.'); ?></p>
				</div>
			</div>
			
		</div>
		</form>
		
		<div id="support" class="seo-panel">
			<h3><?php _e('Tech Support'); ?></h3>
			<?php wp_nonce_field( 'sp_nonce', 'sp_form' ) ?> 
			
			<div class="seo-inner-form">
				<div>
					<h4>Reporting problems</h4>
					<p>If you encounter a problem that appears to be caused by Aceti Dynamic SEO, you may check the bug list at the
					<a href="http://citrinedesign.net/blog/projects/aceti/issues">Aceti Issue Report Page.</a></p>
					<p>Any issues that appear to be bugs/problems in the code will be settled in time if they appear on the Aceti Issue Report Page.
					If you do not see your issue in the list, you may submit a report and it will be addressed for the next version of Aceti.</p>
				</div>
				
				<div>
					<h4>Want to make Aceti Dynamic SEO better?</h4>
					<p>If you'd like to suggest an enhancement to Aceti Dynamic SEO to make it an even better plugin, please visit:
					<a href="http://citrinedesign.net/blog/projects/aceti/enhance">Aceti Enhancement Request Page.</a></p>
				</div>
				
				<div>
					<h4>Donations</h4>
					<p>Aceti Dynamic SEO is under the GPL2 license. Your contributions help continued development of this plugin for you and all who use it.</p>
					<p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="YLLSBB4NP9UVA">
							<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</p>
				</div>
				
				<div>
					<input type="button" value="Reset Aceti to Default Settings" class="button-secondary aceti_reset" />
				</div>
			</div>
			<div class="seo-helper">
				<div style="display:block;">
					<h5>Premium Support</h5>
					<p>If you need professional support, you can submit a request to <a href="http://citrinedesign.net/blog/projects/aceti/support">Citrine Design</a> (Aceti's author) to purchase premium support. 
					With this, you can have any errors that occur smoothed out in no time. Citrine Design also provides premium support for installation and
					configuration of Aceti Dynamic SEO and other useful SEO solutions.</p>
				</div>
			</div>
		</div>
		
	</div>
	
</div>
</div>
	<?php

}

/**
 * Process and store settings
 */
function ac_gather_and_store () {
	
	if ( !check_admin_referer( 'sp_nonce', 'sp_form' ) ) return false;

	print_r(get_magic_quotes_gpc());
	print_r($_POST['ac_settings']);
	
	$temp = $_POST['ac_settings'];
	$sp_gather = multidim_to_obj($temp);

	$keyword_gen = array();
	
	foreach ( $_POST['create_keywords'] as $index => $val ) {
		if ($val == '1') $keyword_gen[] = $index;
	}

	$sp_gather->create_keywords = $keyword_gen;
	
	$sp_gather->canonical = ( $_POST['canonical'] == '1' ? 1 : 0 );
	$sp_gather->capitalize = ( $_POST['capitalize'] == '1' ? 1 : 0 );
	$sp_gather->by_hook = ( $_POST['by_hook'] == '1' ? 1 : 0 );
	
	$noindex = array();
	
	foreach ( $_POST['noindex'] as $index => $val ) {
		if ($val == '1') $noindex[] = $index;
	}
	
	$sp_gather->no_index = $noindex;
	
	foreach ( $_POST['expl_settings'] as $index => $val ) {
		$expl_arr = explode( ',', $val );
		$sp_gather->{$index} = array_map( 'trim', $expl_arr );
	}
	
	global $ac_settings;
	$sp_gather->title_rewrite = ( isset($_POST['disable_titles']) && $_POST['disable_titles'] == 1 ? 0 : 1 );
	$sp_gather->version = ACETI_VERSION;
	$sp_gather->do_setup = 0;
	$sp_gather->frags = $ac_settings->frags;
	$sp_gather->activated = $ac_settings->activated;
	
	ac_save_settings ( $sp_gather );

}
add_action('wp_ajax_sp_gather','ac_gather_and_store');
function multidim_to_obj( $input ) {

	if ( is_array($input) ) {
		return (object) array_map( __FUNCTION__, $input );
	} else {
		return htmlspecialchars( stripslashes( $input ) );
	}

}

/**
 * Meta boxes to control content when posting.
 */
function aceti_box () {
	
	$post_types = get_post_types('','names'); 
	foreach ($post_types as $post_type ) {
		if ($post_type != 'nav_menu_item' && $post_type != 'attachment') {
		  add_meta_box( 
			'aceti_box',
			__( 'Aceti Dynamic SEO'),
			'aceti_content',
			$post_type,
			'advanced',
			'high'
			);
		}
	}
	
}
add_action( 'add_meta_boxes', 'aceti_box' );
add_action( 'save_post', 'aceti_save' );
function aceti_content () {
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'aceti_form' );
	global $ac_settings;
	?>
	
	<table border="0" width="100%">
	<tr>
		<td><label for="ac_title"><?php _e('Title'); ?></label></td>
		<td><input style="width: 300px;" type="text" id="ac_title" name="ac_title" value="<?php
		echo ( is_numeric($_GET['post']) ? get_post_meta( $_GET['post'], '_ac_title', true ) : '');
		?>" /></td>
	</tr>
	<tr><td></td><td><p><?php _e('The title of your content.'); ?></p></td></tr>
	<tr>
		<td><label for="ac_description"><?php _e('Description'); ?></label></td>
		<td><textarea style="width: 400px;" id="ac_description" name="ac_description"><?php
		echo ( is_numeric($_GET['post']) ? get_post_meta( $_GET['post'], '_ac_description', true ) : '');
		?></textarea><p style="position: relative; display: inline; bottom: 24px; left: 10px;">Character Count: <span id="dcounter"><?php 
			echo strlen(get_post_meta( $_GET['post'], '_ac_description', true ));
		?></span></p></td>
	</tr>
	<tr><td></td><td><p><?php _e('A concise and accurate description of your content. A couple sentences is perfect.'); ?></p></td></tr>
	<tr>
		<td><label for="ac_keywords"><?php _e('Keywords'); ?></label></td>
		<td><input style="width: 300px;" type="text" id="ac_keywords" name="ac_keywords" value="<?php
		echo ( is_numeric($_GET['post']) ? get_post_meta( $_GET['post'], '_ac_keywords', true ) : '');	
		?>" /></td>
	</tr>
	<tr><td></td><td><p><?php _e('Use only a few of the most relevant keywords. Separate each with a comma.'); ?></p></td></tr>
	<tr>
		<td><label for="sp_description"><?php _e('Disable?'); ?></label></td>
		<td><input type="checkbox" name="exclude_aceti" value="1" <?php if( isset($_GET['post'])) { if(in_array($_GET['post'], $ac_settings->exclude)) echo 'checked="checked"'; } ?> /> <?php _e('Check to exclude this post from Aceti SEO.'); ?></td>
	</tr>
	</table>
	<?php if (!isset($_GET['post'])) echo '<div id="ac_smooth" style="display:none;"></div>'; ?>
	<script>
	(function($) {
	
	$('input#title').blur( function () {
		$('input#ac_title').val($(this).val());
	});

	function loadup () {
		if (!$('#ac_smooth').length) return;
		var mcee = tinyMCE.activeEditor;
		mcee.onKeyUp.add(function (ed, ev) { 
			if (tripping) return;
			var htmlstr = ed.getContent({format: 'text'}).substring(0, 150); 
			
			if ( htmlstr != $('textarea#ac_description').val().substring(0,150) ) {
				$('#ac_smooth').html(htmlstr);
				var htmlstrcleaned = $('#ac_smooth').text();
				$('textarea#ac_description').val(htmlstrcleaned);
				Acetidcounter();
			}
		
		});
	}
	
	$('textarea#ac_description').keyup( function () {
		Acetidcounter();
	});
	
	function Acetidcounter () {
		$('#dcounter').html($('textarea#ac_description').val().length);
	}

	setTimeout(loadup, 8000);
	var tripping = false;
	$('ac_description').focusin( function () {
		tripping = true;
	});
	
	})(jQuery);
	</script>
	<?php
	
}
function aceti_save ( $pid ) {
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	  
	if ( !wp_verify_nonce( $_POST['aceti_form'], plugin_basename( __FILE__ ) ) )
		return;
		
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $pid ) )
			return;
	} else {
		if ( !current_user_can( 'edit_post', $pid ) )
			return;
	} 
	
	if ( isset($_POST['exclude_aceti']) && $_POST['exclude_aceti'] == 1 ) {
		$ac_settings = get_ac_settings();
		if (!in_array($pid, $ac_settings->exclude))
			$ac_settings->exclude[] = $pid;
		ac_save_settings($ac_settings);
	} else {
		$ac_settings = get_ac_settings();
		if (in_array($pid, $ac_settings->exclude)) {
			$ac_settings->exclude = array_diff($ac_settings->exclude, array($pid));
			ac_save_settings($ac_settings);
		}
	}
	
	if ( isset($_POST['ac_description']) && isset($_POST['ac_keywords']) ) {
		
		if ($_POST['ac_title'] != '')
			update_post_meta( $pid, '_ac_title', $_POST['ac_title'] );
	
		if ($_POST['ac_description'] != '')
			update_post_meta( $pid, '_ac_description', $_POST['ac_description'] );
		
		if ($_POST['ac_keywords'] != '')
			update_post_meta( $pid, '_ac_keywords', $_POST['ac_keywords'] );
	
	}
	
}

/**
 *	Default settings
 */
function ac_defaults () {

		$ac_install_settings = new stdClass;
		$ac_install_settings->version	= ACETI_VERSION;
		$ac_install_settings->activated = 0;
		$ac_install_settings->do_setup	= 1;
		$ac_install_settings->by_hook	= 0;
		$ac_install_settings->capitalize= 1;
		$ac_install_settings->title_rewrite = 1;
		$ac_install_settings->articles			= array( array( 'A', 'An', 'And', 'By', 'In', 'Of', 'The', 'To', 'With' ), array( 'a', 'an', 'and', 'by', 'in', 'of', 'the', 'to', 'with' ) );
		$ac_install_settings->exclude	= array( 'is_feed', 'is_comment_feed' );
		$ac_install_settings->is_front_page->title		= 'Welcome! | %blogname%';
		$ac_install_settings->is_front_page->keywords	= 'no, stuffing, recommended';
		$ac_install_settings->is_front_page->description = 'My blog';
		$ac_install_settings->is_single->title		= '%post_title% | %blogname%';
		$ac_install_settings->is_page->title		= '%post_title% | %blogname%';
		$ac_install_settings->is_archive->title		= '%date% | %blogname%';
		$ac_install_settings->is_author->title		= '%post_author_nicename% on %blogname%';
		$ac_install_settings->is_date->title		= '%date% | %blogname%';
		$ac_install_settings->is_time->title		= '%date% | %blogname%';
		$ac_install_settings->is_category->title	= '%category_title% | %blogname%';
		$ac_install_settings->is_tag->title			= '%tax_title% | %blogname%';
		$ac_install_settings->is_tax->title			= '%tax_title% | %blogname%';
		$ac_install_settings->is_search->title		= '%search% | %blogname%';
		$ac_install_settings->is_404->title			= 'Sorry, %request_words% not found | %blogname%';
		$ac_install_settings->is_paged->title		= '%post_title% part - %page% | %blogname%';
		$ac_install_settings->is_posts_page->title	= '%post_title% | %blogname%';
		$ac_install_settings->no_index	= array( 'is_feed', 'is_comment_feed', 'is_search', 'is_archive', 'is_category', 'is_tag', 'is_tax' );
		$ac_install_settings->exclude_segment = array( 'forum' );
		$ac_install_settings->canonical	= 1;
		$ac_install_settings->append_keywords = '';
		$ac_install_settings->description = '%description%';
		$ac_install_settings->create_keywords = array( 'post_tag', 'category' );
		$ac_install_settings->frags = array('<?php global $wp_query; echo $wp_query->post->post_title; ?>','<?php $cats = get_the_category(); echo $cats["name"]; ?>','<?php echo get_bloginfo("description"); ?>', '<?php global  $wp_query; $ud = get_userdata($wp_query->post->post_author); echo $ud->user_login; ?>', '<?php global  $wp_query; $ud = get_userdata($wp_query->post->post_author); echo $ud->user_nicename; ?>', '<?php global  $wp_query; $ud = get_userdata($wp_query->post->post_author); echo $ud->user_firstname; ?>', '<?php global  $wp_query; $ud = get_userdata($wp_query->post->post_author); echo $ud->user_lastname; ?>','<?php echo single_term_title("", false); ?>','<?php echo category_description(); ?>', '<?php global  $wp_query; if ($wp_query->is_year) $d = "Y"; if ($wp_query->is_month) $d = "F Y";  echo get_the_date($d); ?>','<?php echo single_term_title("", false); ?>','<?php echo tag_description(); ?>','<?php echo get_search_query(); ?>', '<?php echo $_SERVER[REQUEST_URI]; ?>', '<?php echo urldecode($_SERVER[REQUEST_URI]); ?>', '<?php global $paged; echo $paged; ?>','<?php echo single_term_title("", false); ?>','<?php echo single_term_title("", false); ?>');

		return $ac_install_settings;
}

function aceti_reset () {

	$resetsettings = ac_defaults();
	ac_save_settings ( $resetsettings );

}
add_action('wp_ajax_aceti_reset','aceti_reset');

function aceti_switch () {
	
	$ac_settings = get_ac_settings();
	$ac_settings->activated = ( $_POST['ac_onoff'] == '1' ? 1 : 0 );
	ac_save_settings ( $ac_settings );

}
add_action('wp_ajax_aceti_switch','aceti_switch');
?>