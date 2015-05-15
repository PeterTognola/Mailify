<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Mailify {

    /**
     * The single instance of Mailify.
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct ( $file = '', $version = '1.0.0' ) {
        $this->_version = $version;
        $this->_token = 'mailify';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        $this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook( $this->file, array( $this, 'install' ) );
        register_activation_hook($this->file, array($this, 'addWordpressContactTable'));

        // Load frontend JS & CSS
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

        // Load admin JS & CSS
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
        
        //Setup menu
        add_action( 'admin_menu', array( $this, 'addWordpressMenu' ), 10 );

        // Load API for generic admin functions
        if ( is_admin() ) {
            $this->admin = new Mailify_Admin_API();
        }

        // Handle localisation
        $this->load_plugin_textdomain();
        add_action( 'init', array( $this, 'load_localisation' ), 0 );
    } // End __construct ()

    /**
     * Wrapper function to register a new post type
     * @param  string $post_type   Post type name
     * @param  string $plural      Post type item plural name
     * @param  string $single      Post type item single name
     * @param  string $description Description of post type
     * @return object              Post type class object
     */
    public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '' ) {

        if ( ! $post_type || ! $plural || ! $single ) return;

        $post_type = new Mailify_Post_Type( $post_type, $plural, $single, $description );

        return $post_type;
    }

    /**
     * Wrapper function to register a new taxonomy
     * @param  string $taxonomy   Taxonomy name
     * @param  string $plural     Taxonomy single name
     * @param  string $single     Taxonomy plural name
     * @param  array  $post_types Post types to which this taxonomy applies
     * @return object             Taxonomy class object
     */
    public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array() ) {

        if ( ! $taxonomy || ! $plural || ! $single ) return;

        $taxonomy = new Mailify_Taxonomy( $taxonomy, $plural, $single, $post_types );

        return $taxonomy;
    }

    /**
     * Load frontend CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function enqueue_styles () {
        wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
        wp_enqueue_style( $this->_token . '-frontend' );
    } // End enqueue_styles ()

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function enqueue_scripts () {
        wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
        wp_enqueue_script( $this->_token . '-frontend' );
    } // End enqueue_scripts ()

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_styles ( $hook = '' ) {
        wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
        wp_enqueue_style( $this->_token . '-admin' );
    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_scripts ( $hook = '' ) {
        wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
        wp_enqueue_script( $this->_token . '-admin' );
    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_localisation () {
        load_plugin_textdomain( 'mailify', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_plugin_textdomain () {
        $domain = 'mailify';

        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    } // End load_plugin_textdomain ()

    /**
     * Main Mailify Instance
     *
     * Ensures only one instance of Mailify is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see Mailify()
     * @return Main Mailify instance
     */
    public static function instance ( $file = '', $version = '1.0.0' ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $file, $version );
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function install () {
        $this->_log_version_number();
        
    } // End install ()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number () {
        update_option( $this->_token . '_version', $this->_version );
    } // End _log_version_number ()
    
    
    /**
     * Add Mailify menu.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function addWordpressMenu() {
        //add an item to the menu
        add_menu_page (
            'Mailify',
            'Mailify',
            'manage_options',
            'mailify-create',
            array( $this, 'addAdminPage' ),
            plugin_dir_url( __FILE__ ).'icons/my_icon.png',
            '23.56'
        );
    }
    
    /**
     * Add Mailify menu.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    function mailify_tag( $atts ) {
        // Attributes
        extract( shortcode_atts(
            array(
                'id' => 'default_value',
            ), $atts )
        );
        echo 'test';
        
        return 'test';
    }
    
    private function addWordpressContactTable() {
        global $wpdb;
        
        $table_name = 'mailify';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            data text NOT NULL
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    private function insertWordpressContactForm($name, $data) {
        global $wpdb;
        
        //$table_name = $wpdb -> prefix . 'mailify';
        $table_name = 'mailify';

        $sql = "INSERT INTO $table_name SET name=$name, data=$data;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    /**
     * Add Mailify admin page.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function addAdminPage() {
        add_shortcode('mailify', 'mailify_tag');
        if (!shortcode_exists('mailify')) {
            echo 'ERROR: Shortcode not enabled, please enable in functions.php.';
        }
        $this -> addWordpressContactTable();
        if (isset($_GET['createForm'])) { $this -> createAdminPage(); return null; }
        ?>
        <div class="wrap">
            <h2>Mailify - Options <a href="?page=mailify-create&createForm=" class="add-new-h2">Add New</a></h2>
            <div>
                <form method="get">
                    <p class="search-box">
                        <label class="screen-reader-text" for="mailify-search-form">Search Forms:</label>
                        <input type="search" id="mailify-search-form" name="s" value="">
                        <input type="submit" id="search-submit" class="button" value="Search Forms">
                    </p>
                </form>
                <table class="form-table">
                    <tbody>
                    Copy the shortcode from the textboxe's to the right and paste anywhere on a page.
                    <?php
                        $directory = plugin_dir_path( __FILE__ ) . '/forms/';
                        $scanned_directory = array_diff(scandir($directory), array('..', '.'));
                        foreach ($scanned_directory as $dir => $value) {
                            ?>
                        <tr>
                            <th scope="row"><label for="formname"><?php echo explode(".", $value)[0]; ?></label></th>
                            <td><input type="text"  value='[mailify id="<?php echo explode(".", $value)[0]; ?>"]' class="regular-text" /></td>
                        </tr>
                            <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add Mailify admin create page.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function createAdminPage() { //todo check if user is a admin
        if (isset($_POST['formname'])) {
            $this -> file_write_contents(plugin_dir_path( __FILE__ ) . '/forms/' . $_POST['formname'] . '.txt', $_POST['formcode']);
             //mailify_tag(explode(".", $_POST['formname'])[0])
        }
        /*<script> include 'incl/create.js'</script>
        <style>include 'incl/create.css'; </style>*/
        
        include plugin_dir_path( __FILE__ ) . '/../assets/php/view-admin-panel.php';
    }
    
    //private function mailify_tag($atts) {
    //    $a = shortcode_atts( array(
    //        'id' => 'id',
    //    ), $atts);

    //    return $a['id'];
    //}
    
    private function file_force_contents($dir, $contents){
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) mkdir($dir);
        file_put_contents("$dir/$file", $contents);
    }
    
    private function file_write_contents($dir, $contents) {
        $my_file = $dir;
        $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);
        $data = $contents;
        fwrite($handle, $data);
        fclose($handle);
    }
}

