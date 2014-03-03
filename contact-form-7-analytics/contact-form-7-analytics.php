<?php
/**
 * Plugin Name: Contact form 7 - Analytics
 * Plugin URI: http://vivait.co.uk
 * Description: Provides the ability to track contact leads through Contact form 7
 * Version: 0.1
 * Author: Lewis Wright (Viva IT Ltd.)
 * Author URI: http://vivait.co.uk
 * License: BSD
 */


if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

define('CF7A_DIR', dirname(__FILE__));
define('CF7A_LOCAL_NAME', 'cf7a');

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

add_action('wp_footer','cf7a_addfoot');

function cf7a_addfoot() {
    $options = get_option('wp7a'); ?>
	<script>
	jQuery( document ).ajaxSuccess(function( event, xhr, settings ) {
		var response = jQuery.parseJSON(xhr.responseText);

		if (settings.data.indexOf('_wpcf7') > -1 && response.mailSent) {
            var location = window.location.protocol +
                '//' + window.location.hostname +
                window.location.pathname + 
                window.location.search,
                page = '<?php echo htmlspecialchars($options['page_suffix'], ENT_QUOTES) ?>';

            if (typeof _gaq == 'array') {
                _gaq.push(['_trackPageview', location + page]);
            }
            else if (typeof ga == 'function') {
                ga('send', 'pageview', location + page);
            }
		}
	});
	</script>
<?php } 

class Vivait_CF7A_Settings {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'CF7 Analytics', 
            'manage_options', 
            'cf7a-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wp7a' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Contact Form 7 Analytics - Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wp7a_group' );   
                do_settings_sections( 'cf7a-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'wp7a_group', // Option group
            'wp7a', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'CF7 Analytics', // Title
            array( $this, 'print_section_info' ), // Callback
            'cf7a-admin' // Page
        );  

        add_settings_field(
            'page_suffix', // ID
            'Conversion page suffix', // Title 
            array( $this, 'page_suffix_callback' ), // Callback
            'cf7a-admin', // Page
            'setting_section_id' // Section           
        );      

        /*add_settings_field(
            'label', 
            'Label', 
            array( $this, 'label_callback' ), 
            'cf7a-admin', 
            'setting_section_id'
        );*/      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['page_suffix'] ) )
            $new_input['page_suffix'] = sanitize_text_field( $input['page_suffix'] );

        if( isset( $input['label'] ) )
            $new_input['label'] = sanitize_text_field( $input['label'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function page_suffix_callback()
    {
        printf(
            '<input type="text" id="page_suffix" name="wp7a[page_suffix]" value="%s" />',
            isset( $this->options['page_suffix'] ) ? esc_attr( $this->options['page_suffix']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function label_callback()
    {
        printf(
            '<input type="text" id="label" name="wp7a[label]" value="%s" />',
            isset( $this->options['label'] ) ? esc_attr( $this->options['label']) : ''
        );
    }
}

if( is_admin() )
    new Vivait_CF7A_Settings();

?>
