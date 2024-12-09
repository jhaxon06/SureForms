<?php
/**
 * Plugin Name:           AutomatorWP - SureForms
 * Plugin URI:            https://automatorwp.com/add-ons/sureforms/
 * Description:           Connect AutomatorWP with SureForms.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-sureforms
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SureForms
 * @author                AutomatorWP
 */

final class AutomatorWP_SureForms {

    /**
     * @var         AutomatorWP_SureForms $instance The one true AutomatorWP_SureForms
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_SureForms self::$instance The one true AutomatorWP_SureForms
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_SureForms();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_SUREFORMS_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_SUREFORMS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_SUREFORMS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_SUREFORMS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {
           
            require_once AUTOMATORWP_SUREFORMS_DIR . 'includes/functions.php';
            // Includes
            require_once AUTOMATORWP_SUREFORMS_DIR . 'includes/triggers/submit-form.php';
            require_once AUTOMATORWP_SUREFORMS_DIR . 'includes/triggers/anonymous-submit-form.php';
        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'sureforms', array(
            'label' => 'SureForms',
            'icon'  => AUTOMATORWP_SUREFORMS_URL . 'assets/sureforms.svg',
        ) );
    }








    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>
    
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - SureForms requires %s and %s in order to work. Please install and activate them.', 'automatorwp-sureforms' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/sureforms/" target="_blank">SureForms</a>'
                    ); ?>
                </p>
            </div>
    
            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>
    
        <?php endif;
    
    }
    
    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            error_log( 'AutomatorWP no esta activo o instalado' );
            return false;
        }
    
        if ( ! defined( 'SRFM_BASENAME' ) ) {
            error_log( 'SureForms no esta activo o instalado.' );
            return false;
        }
    
        return true;
    }
    

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

      

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_SureForms instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_SureForms The one true AutomatorWP_SureForms
 */
function AutomatorWP_SureForms() {
    return AutomatorWP_SureForms::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_SureForms' );
