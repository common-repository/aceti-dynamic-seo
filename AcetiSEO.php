<?php 
/**
 *	AcetiSEO
 */
class AcetiSEO {

	//	Store content type
	var $content_type;

	//	Possible content types
	var $possible_types = array('is_category','is_tag','is_tax','is_home','is_page','is_single','is_author','is_archive','is_date','is_search','is_404','is_paged','is_posts_page');
	
	//	Output buffer or hook? 
	var $by_hook;
	
	//	Content ID
	var $the_id;
	
	//	The generated/found title text
	var $the_title;
	
	//	The generated meta tag array
	var $the_meta;
	
	//	The blog's name
	var $blogname;
	
	//	Accepted tags
	var $tags = array('%post_title%','%post_category%','%blog_description%', '%post_author_login%', '%post_author_nicename%', '%post_author_firstname%', '%post_author_lastname%','%category_title%','%category_description%', '%date%', '%post_tag%','%tag_description%','%search%', '%request_url%', '%request_words%', '%page%','%tax_title%','%tag_title%','%blogname%');
	
	//	Replace tags with...wish everyone would just use PHP 5.3
	var $tags_replace;
	
	/**
	 * Construct
	 */
	function AcetiSEO () {
		
		global $wp_query, $ac_settings;
		$keys_arr = array_keys( (array) $wp_query, '1' );
		$type = array_values( array_intersect( $this->possible_types, $keys_arr ) );
		
		$this->by_hook = $ac_settings->by_hook;
		$this->content_type = ( is_front_page() ? 'is_front_page' : $type[0] );
		$this->the_id = ( !isset($wp_query->queried_object_id) ? $wp_query->queried_object->ID : $wp_query->queried_object_id );
		$this->blogname = get_bloginfo( 'name' );

		$this->tags_replace = $ac_settings->frags;
		$this->tags_replace[] = $this->blogname;

	}
	
	/**
	 * Start doing stuff.
	 */
	function operate () {

		global $ac_settings;
		
		$type = ( is_front_page() ? 'is_front_page' : $this->content_type );

		//	Check if this type is managed by plugin or if this ID is excluded from being managed.
		$segment_support = false;
		if ( !empty( $ac_settings->exclude_segment ) ) {
			foreach ( $ac_settings->exclude_segment as $segment ) {
				if ( !$segment_support ) $segment_support = (bool) strpos($_SERVER['REQUEST_URI'], $segment);
			}
		}

		if ( in_array( get_post_type(), $ac_settings->exclude ) || in_array( $this->the_id, $ac_settings->exclude ) || in_array( $this->content_type, $ac_settings->exclude ) || $segment_support ) return;

		//	Else carry on
		if ( $ac_settings->title_rewrite ) $this->generate_title( $ac_settings->{$type}->title );
		$this->generate_metas();

	}
	
	/**
	 * Parse and serve up a title.
	 *
	 * @param string $tag The title format string.
	 */
	function generate_title ( $tag = '' ) {
	
		str_replace('?php', '', $this->the_title); // JIC...
		if ( $tag == ''	) {
			$this->the_title = $this->blogname;
		} else {
			if (get_post_meta( $this->the_id, '_ac_title', true )) $this->the_title = str_replace( '%post_title%', get_post_meta( $this->the_id, '_ac_title', true ), $tag );
			$this->the_title = str_replace( $this->tags, $this->tags_replace, $tag );
		}
		
		$this->the_title = $this->grab( $this->the_title );
			
		global $ac_settings;
		if ( $ac_settings->capitalize ) {
			$this->the_title = ucwords( strtolower($this->the_title) );
			$this->the_title = str_replace( $ac_settings->articles[0], $ac_settings->articles[1], $this->the_title );
			$this->the_title = ucfirst( $this->the_title );
		}
			
		$this->the_title = '<title>' . $this->the_title . '</title>';
		
		if ( $this->by_hook ) {
		
			add_action( 'wp_head', array( &$this, 'title_by_action') );
			add_action( 'aceti_head', array( &$this, 'title_by_action') );
			
		} else {
		
			ob_start( array( &$this, 'title_by_output' ) );
			
		}
	}
	
	/**
	 * Functions to output custom <title></title>
	 */
	 function title_by_output ( $str ) {
		return preg_replace( '/(<title>.+?)+(<\/title>)/i', $this->the_title , $str, 1 );
	 }
	 function title_by_action() { 
		$this->the_meta[] = $this->the_title; 
	 }
	 
	 /**
	  * Evaluate the parts we need...can't wait until PHP 5.3+ is everywhere.
	  */
	 function grab ( $ev ) {
		if ( (bool) strpos( $ev, '?php' ) ) {
			ob_start();
			$ev = eval( '?>' . $ev );
			$ev = ob_get_contents();
			ob_end_clean();
			return $ev;
		} else {
			return $ev;
		}
	 }
	 
	 /**
	  * Generate meta tags.
	  */
	 function generate_metas () {
	
		global $ac_settings;
		//	Noindex?
		if ( in_array( $this->content_type, $ac_settings->no_index ) ) {
		
			$this->the_meta['noindex'] = '<meta name="robots" content="noindex,follow" />';
			add_action( 'wp_head', array( &$this, 'output_meta') );
			add_action( 'aceti_head', array( &$this, 'output_meta') );
			return;
			
		}
		//	Canonical?
		if ( $ac_settings->canonical && is_singular() && ($this->content_type != 'is_404') ) {
			$this->the_meta[] = '<link rel="canonical" href="' . untrailingslashit(get_permalink()) . '" />';
		}
		// Index?
		if ( is_home() ) {
			$this->the_meta[] = '<link rel="index" href="' . untrailingslashit(home_url()) . '" />';
		}
		//	Keywords
		$this->the_meta[] = '<meta name="keywords" content="' . $this->get_keywords() . '" />';
		//	Description
		$this->the_meta[] = '<meta name="description" content="' . $this->get_description() . '" />';
		
		add_action( 'wp_head', array( &$this, 'output_meta') );
		add_action( 'aceti_head', array( &$this, 'output_meta') );
		
	 }
	 
	 /**
	  *	Get/create keywords
	  */
	 function get_keywords () {
	 
		global $ac_settings;
		if ( $this->content_type == 'is_front_page' ) return  $ac_settings->is_front_page->keywords;
		
		if ( $keywords = get_post_meta( $this->the_id, '_ac_keywords', true ) ) {
			return ( $ac_settings->append_keywords ? $keywords . ', ' . $ac_settings->append_keywords : $keywords ); 
		} else {

			$keywords = implode(', ', wp_get_object_terms($this->the_id, $ac_settings->create_keywords, array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'names')));
			
			update_post_meta( $this->the_id, '_ac_keywords', $keywords );
			return ( $ac_settings->append_keywords ? $keywords . ', ' . $ac_settings->append_keywords : $keywords );
				
		}
	 }
	 
	 /**
	  *	Get/create description
	  */
	 function get_description () {
	 
		global $ac_settings, $post;
		if ( $this->content_type == 'is_front_page' ) return  $ac_settings->is_front_page->description;
		
		$describe = get_post_meta( $this->the_id, '_ac_description', true );
		if ( !$describe ) {
		
			if ($post->post_excerpt) {
				$describe = strip_tags(substr( $post->post_excerpt, 0, 200 ));
			} elseif ( $post->post_content ) {
				$describe = strip_tags(substr( $post->post_content, 0, 200 ));
			}
			$describe = str_replace(array("\n","\n\r","\r\n","\r"), array(' ',' ',' ',' '), $describe);
			update_post_meta( $this->the_id, '_ac_description', $describe );
		}
		
		$description = str_replace( '%description%', $describe , $ac_settings->description );
		$description = str_replace('?php', '', $description); // JIC...
		$description = str_replace( $this->tags, $this->tags_replace, $description );
		$description = $this->grab( $description );
		return $description;
		
	 }

	 /**
	  * Output SP meta tags string in wp_head() or aceti_head().
	  */ 
	 function output_meta () {
	 
		$meta_string = '';
		foreach ( $this->the_meta as $meta_tag ) {
			$meta_string .= $meta_tag . PHP_EOL;
		}
	 
		$meta_string = '<!-- SEO Generated with Aceti SEO by citrinedesign.net -->' . PHP_EOL . $meta_string . '<!-- SEO Generated with Aceti SEO -->' . PHP_EOL;
		
		echo $meta_string;
		
	 }
}
?>