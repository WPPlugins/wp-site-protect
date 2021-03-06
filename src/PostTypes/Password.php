<?php

namespace mowta\SiteProtect\PostTypes;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

use \mowta\SiteProtect\Models\Password as SinglePassword;

class Password extends PostType {

	public function __construct() {
		parent::__construct( 'password' ); 
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_filter( 'manage_password_posts_columns', array( $this, 'password_columns' ) );
		add_action( 'manage_password_posts_custom_column', array( $this, 'password_columns_content' ), 10, 2 );

		add_action('carbon_after_save_post_meta', array( $this, 'save_hashed_password' ) );

		add_filter('posts_join', array( $this, 'search_join') );
		add_filter('posts_where', array( $this, 'search_where') );
		add_filter( 'posts_distinct', array( $this, 'search_distinct' ) );



	}
	
	public function register() {
		$labels = array(
			'name'                  => __( 'Passwords', 'wp-site-protect' ),
			'singular_name'         => __( 'Password', 'wp-site-protect' ),
			'menu_name'             => __( 'Site Passwords', 'wp-site-protect' ),
			'name_admin_bar'        => __( 'Site Passwords', 'wp-site-protect' ),
			'archives'              => __( 'Password Archies', 'wp-site-protect' ),
			'parent_item_colon'     => __( 'Parent Password', 'wp-site-protect' ),
			'all_items'             => __( 'All Passwords', 'wp-site-protect' ),
			'add_new_item'          => __( 'Add New Password', 'wp-site-protect' ),
			'add_new'               => __( 'Add New', 'wp-site-protect' ),
			'new_item'              => __( 'New Password', 'wp-site-protect' ),
			'edit_item'             => __( 'Edit Password', 'wp-site-protect' ),
			'update_item'           => __( 'Update Password', 'wp-site-protect' ),
			'view_item'             => __( 'View Password', 'wp-site-protect' ),
			'search_items'          => __( 'Search Password', 'wp-site-protect' ),
			'not_found'             => __( 'Not found', 'wp-site-protect' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wp-site-protect' ),
			'featured_image'        => __( 'Featured Image', 'wp-site-protect' ),
			'set_featured_image'    => __( 'Set featured image', 'wp-site-protect' ),
			'remove_featured_image' => __( 'Remove featured image', 'wp-site-protect' ),
			'use_featured_image'    => __( 'Use as featured image', 'wp-site-protect' ),
			'insert_into_item'      => __( 'Insert into passwords', 'wp-site-protect' ),
			'uploaded_to_this_item' => __( 'Uploaded to this password', 'wp-site-protect' ),
			'items_list'            => __( 'Passwords list', 'wp-site-protect' ),
			'items_list_navigation' => __( 'Passwords list navigation', 'wp-site-protect' ),
			'filter_items_list'     => __( 'Filter passwords list', 'wp-site-protect' ),
		);
		$capabilities = array(
			'edit_post'             => 'manage_options',
			'read_post'             => 'manage_options',
			'delete_post'           => 'manage_options',
			'delete_posts'          => 'manage_options',
			'edit_posts'            => 'manage_options',
			'edit_others_posts'     => 'manage_options',
			'publish_posts'         => 'manage_options',
			'read_private_posts'    => 'manage_options',
		);
		$args = array(
			'label'                 => __( 'Password', 'wp-site-protect' ),
			'description'           => __( 'Site Passwords', 'wp-site-protect' ),
			'labels'                => $labels,
			'supports'              => array( 'revisions' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-lock',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capabilities'          => $capabilities,
		);

		register_post_type( $this->getName(), $args );
	}

	public function fields() {
		Container::make('post_meta', __('Create Password', 'wp-site-protect') )
			->show_on_post_type('password')
			->add_fields(array(
				Field::make('text', '_password', __( 'Password', 'wp-site-protect' ))
					->set_default_value( wp_generate_password( 8 ) )
					->help_text( __("By default, an autogenerated password will be filled.", 'wp-site-protect') ),
				Field::make('checkbox', '_regenerate', __( 'Force a new password on first use', 'wp-site-protect' ))
					->set_default_value('yes')
					->help_text( __("If you want this password to be temporary, check this.", 'wp-site-protect') ),
			));
	}

	public function password_columns( $defaults ) {
		return array( 
				'cb' => "<input type=\"checkbox\" />",
				'password' => __('Password', 'wp-site-protect'),
				'status' => __('Status', 'wp-site-protect'),
				'last_used' => __('Last Login', 'wp-site-protect'),
			);
	}

	public function password_columns_content( $column_name, $post_id ) {
		$password = new SinglePassword( $post_id );
		switch ( $column_name ) {
			case 'password':
				echo "<a class=\"row-title\" href='" . esc_url( get_edit_post_link( $post_id ) ) . "'>" . $password->get_original_password() . "</a>";
				break;
			case 'status':
				if ( $password->used() ) {
					echo sprintf( __('Password changed to <strong>%s</strong>', 'wp-site-protect'), $password->get_current_password() );
				} else {
					echo __('Not being used.', 'wp-site-protect');
				}
				break;
			case 'last_used':
				if ( $password->used() ) {
					echo $password->get_meta('_last_time_used') . "<br/>(" . $password->get_meta('_last_time_ip') . ")";
				} else {
					echo __('Never logged in.', 'wp-site-protect');
				}
				break;
		}
	}

	public function register_metabox() {
		if ( $this->current_post_exists() ) {
			add_meta_box( 'password-details', __( 'Password Details', 'wp-site-protect' ), array( $this, 'render_password_details_metabox' ), 'password' );
		}
	}

	public function render_password_details_metabox( $post, $metabox) {
		
		$password = new SinglePassword( $post );
		?>
		<p><strong><?php esc_html_e('Original Password:', 'wp-site-protect') ?></strong> <?php echo $password->get_original_password() ?>
		<p><strong><?php esc_html_e('Password Hash:', 'wp-site-protect') ?></strong> <?php echo $password->get_hashed_password() ?></p>
		<?php if ( ! $password->used() ) :?>
			<p><?php _e('This password was never been used.', 'wp-site-protect' ) ?></p>
		<?php else: ?>
			<p><strong><?php esc_html_e('First time used:', 'wp-site-protect') ?> </strong>
				<?php echo $password->get_meta('_first_time_used') ?> (<?php echo $password->get_meta('_first_time_ip') ?>)</p>
			<p><strong><?php esc_html_e('Last time used:', 'wp-site-protect') ?> </strong>
				<?php echo $password->get_meta('_last_time_used') ?> (<?php echo $password->get_meta('_last_time_ip') ?>)</p>
			<?php if( $password->get_meta('_changed_on') ): ?>
			<p><strong><?php esc_html_e('Changed on:', 'wp-site-protect') ?> </strong>
				<?php echo $password->get_meta('_changed_on') ?> (<?php echo $password->get_meta('_changed_by') ?>)</p>
			<?php endif; ?>
		<?php endif;

	}

	private function current_post_exists() {
		return isset( $_GET['post'] ) && $_GET['post'] && get_post($_GET['post']);
	}

	public function save_hashed_password( $post_id ) {
		$post = get_post( $post_id );

		if( $post->post_type != 'password' )
			return;

		$password = carbon_get_post_meta($post_id, '_password');
		$hashed  = carbon_get_post_meta($post_id, '_hashed_password');

		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		$wp_hasher = new \PasswordHash(8, TRUE);

		if( ! $wp_hasher->CheckPassword( $password, $hashed ) ) {
			update_post_meta($post_id, '_hashed_password', wp_hash_password( $password ) );
		}

		// Purge the cache
		delete_transient( 'wpsp_password_' . $hashed );
	}


	function search_join( $join ) {

		if ( ! is_admin() )  {
			return $join;
		}

		global $wpdb, $wp_query;

		if ( is_search() && $wp_query->get( 's' )  && 'password' == $wp_query->get( 'post_type' ) ) {
			$join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;
	}

	function search_where( $where ) {

		if ( ! is_admin() )  {
			return $where;
		}

		global $wpdb, $wp_query;

		if ( is_search() && $wp_query->get( 's' )  && 'password' == $wp_query->get( 'post_type' ) ) {
			$where = preg_replace(
				"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(" . $wpdb->posts . ".post_title LIKE $1) OR ( {$wpdb->postmeta}.meta_key='_password' AND {$wpdb->postmeta}.meta_value LIKE $1) 
				OR ( {$wpdb->postmeta}.meta_key='_original_password' AND {$wpdb->postmeta}.meta_value LIKE $1)", $where );
		}

		return $where;
	}

	function search_distinct( $distinct ) {

		if ( ! is_admin() )  {
			return $distinct;
		}

		global $wpdb, $wp_query;

		if ( is_search() && $wp_query->get( 's' )  && 'password' == $wp_query->get( 'post_type' ) ) {
			return "DISTINCT";
		}

		return $distinct;
	}
}