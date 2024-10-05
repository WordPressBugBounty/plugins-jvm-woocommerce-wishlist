<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Instantiation.
 * Boilerplate child-class to extend onboarding wizard class.
 */

/**
 * Onboarding namespace.
 *
 * @todo MUST REPLACE AND USE OWN NAMESPACE.
 */
namespace CIXW_WISHLIST\My_Feature;

use TheWebSolver\Core\Admin\Onboarding\Wizard;
use CIXW_WISHLIST\Settings;


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard configuration.
 *
 * {@see @method Config::onboarding()}
 *
 * This extends the main Wizard class.
 * Use this as a boilerplate for creating own onboarding wizard.
 */
class Onboarding_Wizard extends Wizard {
	/**
	 * Current config instance.
	 *
	 * @var Config Overriding property on child for IDE/Code Editor support.
	 *
	 * @since 1.1
	 */
	protected $config;

	// add init function
	public function init() {
		add_action( 'cix_onboarding_wizard_save', array( $this, 'save_to_plugin_settings' ), 10, 2 );
		add_filter( 'cix_onboarding_wizard_option', array( $this, 'onboarding_option' ) );
		add_filter( 'hzfex_onboarding_wizard_ready', array( $this, 'set_ready' ),10,2 );
		parent::init();
	}
	// set_ready function
	public function set_ready( $content, $prefix ) {
		if ( 'cixww_onboarding' === $prefix ) {
			$content['title'] = 'Congratulations 🎉 ';
			$content['desc'] = 'Well done! You\'ve set up the basic Wishlist settings. For more customization options, visit the WooCommerce Wishlist Plugin Settings page. Check out our Online Documentation for more detailed information on the available options.';
		}
		return $content;
	}
	// onboarding_option function
	public function onboarding_option( $option ) {

		return 'cixwishlist_settings';
	}
	/**
	 * Save onboarding data to plugin settings.
	 *
	 * @param string $step Current step.
	 * @param array  $data Step data.
	 */
	public function save_to_plugin_settings( $step, $data ) {
		$plugin_option = get_option( 'cixwishlist_settings' ); // codestart setting key
		$options       = array_merge( $plugin_option, $data );

		update_option( 'cixwishlist_settings', $options );
	}
	protected function reset() {
		// Every option is set to false here so nothing gets deleted.
		// true will delete option, false will not.
		$this->reset = array(
			'dependency_name'            => false,
			'dependency_status'          => false,
			'recommended_status'         => false,
			'recommended_checked_status' => false,
		);
	}


	// protected function set_dependency() {
	// $this->slug     = 'query-monitor';
	// $this->filename = 'query-monitor'; // Not needed as it is same as slug. Included as an example.
	// $this->version  = '3.6.0'; // Not needed if latest to install. Can be: '3.3.3', '2.17.0', '2.6.9' etc (https://plugins.trac.wordpress.org/browser/query-monitor/#tags).
	// }

	/**
	 * Sets onboarding HTML head title.
	 *
	 * @todo Change your onboarding title.
	 * @inheritDoc
	 */
	protected function set_title() {
		$this->title       = __( 'Wishlist for WooCoomerce &rsaquo; Setup Wizard', 'tws-onboarding' );
		$this->intro_title = __( 'Welcome to the Wishlist for WooCommerce Setup Wizard!', 'tws-onboarding' );
		$this->intro_desc  = '
		<p>We\'re excited to have you on board as you take the first step towards enhancing your store. Our Wishlist plugin is designed to provide your customers with an intuitive and user-friendly way to save and keep track of their favorite products on your website.</p> 

		<p>To get started, we have created a quick setup wizard that will guide you through the process of configuring the basic settings of the plugin. This wizard is easy to use and will ensure that you have a smooth and seamless experience setting up the plugin. </p>

		';
	}

	/**
	 * Sets onboarding logo.
	 *
	 * @todo Set your own onboarding logo args.
	 * @inheritDoc
	 */
	protected function set_logo() {
		$this->logo = array(
			'href'   => get_admin_url( get_current_blog_id() ),
			'alt'    => 'Wishlist for WooCoomerce',
			'width'  => '135px',
			'height' => 'auto',
			'src'    => '', // $this->config->get_url() . 'Assets/onboarding.svg',
		);
	}

	/**
	 * Onboarding steps.
	 *
	 * @todo Set your own onboarding steps.\
	 *       `Introduction`, `Recommended` and `Ready` steps have action and filter hooks to change the contents.
	 * @inheritDoc
	 */
	protected function set_steps() {
		$steps = array(
			'general'       => array(
				'name' => __( 'General Settings', 'tws-onboarding' ),
				'view' => array( $this, 'general_view' ),
				'save' => array( $this, 'general_save' ),
			),
			'popup'         => array(
				'name' => __( 'Popup', 'tws-onboarding' ),
				// Disabling description for this step.
				// 'desc' => __( 'Radio and select dropdown form fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ), // phpcs:ignore -- Valid Code OK.
				'view' => array( $this, 'popup_form_view' ),
				'save' => array( $this, 'popup_form_save' ),
			),
			'button'        => array(
				'name' => __( 'Button', 'tws-onboarding' ),
				// Disabling description for this step.
				// 'desc' => __( 'Radio and select dropdown form fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ), // phpcs:ignore -- Valid Code OK.
				'view' => array( $this, 'button_view' ),
				'save' => array( $this, 'button_save' ),
			),
			'wishlist_page' => array(
				'name' => __( 'Wishlist Page', 'tws-onboarding' ),
				// Disabling description for this step.
				// 'desc' => __( 'Radio and select dropdown form fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ), // phpcs:ignore -- Valid Code OK.
				'view' => array( $this, 'wishlist_page_view' ),
				'save' => array( $this, 'wishlist_page_save' ),
			),
		);

		return $steps;
	}

	/**
	 * Set the recommended plugins.
	 *
	 * @todo Manage recommended plugins. Each plugin will be installed and activated on recommended step.
	 *       There will be enable/disbale option whether or not to intall the recommended plugin.
	 *       As an example, 5 plugins as set as recommended plugins.
	 *       If don't have any recommended plugin, delete this method.
	 * @inheritDoc
	 */
	protected function set_recommended_plugins() {
		$plugins = array(

			// array(
			// 'slug'  => 'elementor',
			// 'title' => __( 'Elementor', 'tws-onboarding' ),
			// 'desc'  => __( 'Elementor is the most popular page builder for WordPress.', 'tws-onboarding' ),
			// 'logo'  => 'https://ps.w.org/elementor/assets/icon-256x256.png?rev=2044277',
			// 'alt'   => __( 'Elementor logo', 'tws-onboarding' ),
			// ),
		);

		$this->recommended = $plugins;
	}

	/**
	 * Displays `general` step options.
	 */
	public function general_view() {
		$this->config->form->start();

		?>
		<h1 class="page-heading">General</h1>
		<!-- Form Fields -->
		<?php

		// Text input field.
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'wishlist_name',
				'label'       => 'Default Wishlist Name',
				'placeholder' => 'Enter default wishlist name',
				'default'     => 'My Wishlist',
			)
		);

		// Select options.
		$this->config->form->add_field(
			'select',
			array(
				'id'      => 'create_wishlist_page',
				'label'   => 'Generate Wishlist Page',
				'desc'    => 'Create a wishlist page with shortcode <code>[jvm_woocommerce_wishlist]</code> and set it as the wishlist page.',
				'options' => Settings::get_pages(1),
				'default' => 'gen_page',


			)
		);
		// Select options.
		$this->config->form->add_field(
			'select',
			array(
				'id'      => 'product_button_action',
				'label'   => 'Action after added to Wishlist',
				'default' => 'popup',
				'options' => array(
					'none'     => 'None',
					'redirect' => 'Redirect to Wishlist Page',
					'popup'    => 'Show Popup',
				),
			)
		);

		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons(); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}

	/**
	 * Saves `general` step options.
	 */
	public function general_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$this->config->form->save(
			array(
				'wishlist_name'         => 'text',
				'create_wishlist_page'  => 'select',
				'product_button_action' => 'select',

			)
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Displays `front` step Options.
	 */
	public function popup_form_view() {
		$this->config->form->start();

		?>
		<h1 class="page-heading">Popup</h1>
		<!-- Form Fields -->

		<?php

		// Text input field.
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_view_wishlist_text',
				'label'       => 'View Wishlist Text',
				'placeholder' => '',
				'default'     => 'View Wishlist',
			)
		);
		// add Product Already in Wishlist Text
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_already_in_wishlist_text',
				'placeholder' => '',
				'label'       => 'Product Already in Wishlist Text',
				'desc'        => 'Text to display when the product is already in the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
				'default'     => '{product_name} Already in Wishlist',
			)
		);
		// add Product Added to Wishlist Text
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_added_to_wishlist_text',
				'placeholder' => '',
				'label'       => 'Product Added to Wishlist Text',
				'desc'        => 'Text to display when the product is added to the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
				'default'     => '{product_name} Added to Wishlist',
			)
		);
		// Product Removed from Wishlist Text
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_removed_from_wishlist_text',
				'placeholder' => '',
				'label'       => 'Product Removed from Wishlist Text',
				'desc'        => 'Text to display when the product is removed from the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
				'default'     => '{product_name} Removed from Wishlist',
			)
		);

		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons( true ); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}

	/**
	 * Saves `front` step Options.
	 */
	public function popup_form_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$this->config->form->save(
			// add popup_form_view fields
			array(
				'product_view_wishlist_text'         => 'text',
				'product_already_in_wishlist_text'   => 'text',
				'product_added_to_wishlist_text'     => 'text',
				'product_removed_from_wishlist_text' => 'text',
			)
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}
	/**
	 * Displays `button_` step Options.
	 */
	public function button_view() {
		$this->config->form->start();

		?>
		<h1 class="page-heading">Button</h1>
		<!-- Form Fields -->
		<?php

		// add radio field.
		$this->config->form->add_field(
			'select',
			array(
				'id'      => 'product_button_type',
				'label'   => 'Button Type',
				'default' => 'button',
				'options' => array(
					'button' => 'Button',
					'link'   => 'Link',
				),
			)
		);
		// add check box for button icon
		$this->config->form->add_field(
			'checkbox',
			array(
				'id'      => 'product_button_icon',
				'label'   => 'Show Wishlist Icon',
				'default' => true,
			)
		);
		// add button text field
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_button_text',
				'label'       => 'Button Text',
				'placeholder' => '',
				'default'     => 'Add to Wishlist',
			)
		);
		// "Remove from Wishlist" Text
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_button_remove_text',
				'label'       => 'Remove from Wishlist Text',
				'placeholder' => '',
				'default'     => 'Remove from Wishlist',
			)
		);
		// "Already in wishlist" Text
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'product_button_already_in_text',
				'label'       => 'Already in Wishlist Text',
				'placeholder' => '',
				'default'     => 'Already in Wishlist',
			)
		);

		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons( true ); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}

	/**
	 * Saves `button_` step Options.
	 */
	public function button_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$this->config->form->save(
			// add button_view field
			array(
				'product_button_type'            => 'select',
				'product_button_icon'            => 'checkbox',
				'product_button_text'            => 'text',
				'product_button_remove_text'     => 'text',
				'product_button_already_in_text' => 'text',
			)
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Displays `wishlist_page_` step Options.
	 */
	public function wishlist_page_view() {
		$this->config->form->start();

		?>
		<h1 class="page-heading">Wishlist Page</h1>
		<!-- Form Fields -->
		<?php
		// use ff function to add fields
		$fields = $this->onboarding_options();
		foreach ( $fields['wishlist_page'] as $field ) {
			$this->config->form->add_field(
				$field['type'],
				$field
			);
		}
		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons( true ); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}
	/**
	 * Saves `wishlist_page_` step Options.
	 */
	public function wishlist_page_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.
		$fields      = $this->onboarding_options();
		$save_fields = array();

		foreach ( $fields['wishlist_page'] as $field ) {
			$save_fields[ $field['id'] ] = $field['type'];

		}
		$this->config->form->save(
			$save_fields
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	public function onboarding_options() {

		$fields = array(
			'wishlist_page' => array(
				
				// wishlist page no item text field
				array(
					'id'      => 'wishlist_page_no_item_text',
					'type'    => 'text',
					'title'   => __( 'No Item Text', 'jvm-woocommerce-wishlist' ),
					'default' => 'No items in your wishlist',
				),

				// wishlist page table add to cart text field
				array(
					'id'      => 'wishlist_page_table_add_to_cart_text',
					'type'    => 'text',
					'title'   => __( 'Add to Cart Text', 'jvm-woocommerce-wishlist' ),
					'default' => 'Add to Cart',
				),
				

				// removed_cart_notice notice field
				array(
					'id'      => 'removed_cart_notice',
					'type'    => 'text',
					'title'   => __( 'Removed from Cart Notice', 'jvm-woocommerce-wishlist' ),
					'default' => '{product_name} removed from cart',
					'desc'    => __( 'Removed from cart notice message. Use, placeholder <code>{product_name}</code> to display name of the product.', 'jvm-woocommerce-wishlist' ),
				),

				array(
					'id'      => 'wishlist_page_table_redirect_to_cart',
					'type'    => 'checkbox',
					'title'   => __( 'Redirect to Cart', 'jvm-woocommerce-wishlist' ),
					'desc'    => __( 'Redirect to cart page after adding to cart from wishlist page.', 'jvm-woocommerce-wishlist' ),
					'default' => 1,

				),
				// add switcher for remove if added to cart
				array(
					'id'      => 'wishlist_page_table_remove_if_added_to_cart',
					'type'    => 'checkbox',
					'title'   => __( 'Remove if Added to Cart', 'jvm-woocommerce-wishlist' ),
					'desc'    => __( 'Remove item from wishlist if added to cart.', 'jvm-woocommerce-wishlist' ),
					'default' => 1,

				),
			),
		);
		return $fields;
	}
	/**
	 * Sets steps footer HTML.
	 *
	 * @since 1.0
	 */
	protected function set_step_footer() {
		$steps = array_keys( $this->steps );
		$last  = array_pop( $steps );
		if ( $last === $this->step ) :
			?>
				<!-- footer -->
				<footer id="footer">
					<a
					class="onboarding-return onboarding_dashboard_btn button"
					href="https://www.codeixer.com/docs-category/wishlist-for-wc/" target="_">
						<?php esc_html_e( 'Documentation', 'tws-onboarding' ); ?>
					</a>
					<a
					class="onboarding-return onboarding_dashboard_btn button"
					href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=cixwishlist_settings' )); ?>">
						 <?php esc_html_e( 'Wishlist Settings', 'tws-onboarding' ); ?>
					</a>
				</footer>
				<!-- #footer -->
			<?php endif; ?>
			</main>
			<!-- #main -->
			</body>
			</html>
			<?php
	}
}


