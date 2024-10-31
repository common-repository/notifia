<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://notifia.io/
 * @since      1.0.0
 *
 * @package    Notifia
 * @subpackage Notifia/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Notifia
 * @subpackage Notifia/admin
 * @author     Notifia <hello@notifia.io>
 */
class Notifia_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */


    public static $actions = array(
        'index' => array(
            'slug' => 'notifia',
            'function' => 'action_admin_menu_page',
            'name' => 'Notifia',
            'title' => 'Notifia Settings',
        ),
        'auth' => array(
            'sign-out' => array(
                'slug' => 'notifia_sign_out',
                'function' => 'action_admin_menu_sign_out',
                'name' => 'Sign out',
                'title' => 'Sign out - Notifia',
            ),
        ),
        'guest' => array(
            'sign-in' => array(
                'slug' => 'notifia_sign_in',
                'function' => 'action_admin_menu_sign_in',
                'name' => 'Sign in',
                'title' => 'Sign in to Notifia',
            ),
            'sign-up' => array(
                'slug' => 'notifia_sign_up',
                'function' => 'action_admin_menu_sign_up',
                'name' => 'Sign up',
                'title' => 'Sign up to Notifia',
            ),
        )
    );
    public static $settings = array();
    public static $loginEndpoint = 'https://api.notifia.io/api/v1/public/auth/login';
    public static $loginPath = 'https://notifia.io/auth/login';
    public static $signupPath = 'https://notifia.io/auth/sign-up';
    public static $fetchEndpoint = 'https://api.notifia.io/api/v1/user/me';
    public static $dashboardPath = 'https://notifia.io/dashboard/';
    public static $googleLoginLink = 'https://api.notifia.io/api/v1/oauth/google';
    private static $notifia = null;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_option('notifia_settings', self::$settings);

    }


    /** initialize notifia */

    public static function init($plugin_name, $version)
    {
        if (is_null(self::$notifia)) {
            self::$notifia = new self($plugin_name, $version);
            self::$settings = self::notifia_settings();
            if (empty(self::$settings)) {
                $settings = array(
                    'script_id' => null
                );
                add_option('notifia_settings', $settings);

//                self::install( self::$settings );
            }
        }

        return self::$notifia;
    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/notifia-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/notifia-admin.js', array('jquery'), $this->version, false);

    }

    /**
     *
     * Add Script For Client
     */
    public function add_client_scripts()
    {

        if (self::$settings['script_id']) {
            echo "<script>(function (d,s,i) {
                        var j = d.createElement('script');
                        j.async = true;
                        j.id = 'notifia';
                        j.src = 'https://static.notifia.io/widget.js';
                        j.setAttribute('initialize',i);
                        d.head.appendChild(j);
                    })( document, 'script','" . self::$settings['script_id'] . "');
                    </script>
                <!-- End Notifia -->";

        }
    }


    /**
     * render template
     *
     * @since 1.0.0
     */

    /** render templates */
    protected static function render_template($viewFile, $params = array())
    {
        $path = dirname(__FILE__) . '/partials/' . $viewFile . '.php';

        if (file_exists($path)) {
            foreach ($params as $paramKey => $paramValue) {
                $$paramKey = $paramValue;
            }
            include_once $path;
        } else {
            wp_die('The template file (' . esc_html($viewFile) . '.php) not found!');
        }
    }


    /**
     * Get value from $_GET
     *
     * @since  1.0.0
     */
    protected static function get($param, $allowEmpty = false, $default = null)
    {
        if ((isset($_GET[$param]) && $allowEmpty) || (!empty($_GET[$param]) && !$allowEmpty)) {
            return wp_unslash($_GET[$param]);
        } else {
            return $default;
        }
    }


    /**
     * Get value from $_POST
     *
     * @since  1.0.0
     */
    protected static function post($param, $allowEmpty = false, $default = null)
    {
        if ((isset($_POST[$param]) && $allowEmpty) || (!empty($_POST[$param]) && !$allowEmpty)) {
            return sanitize_text_field(wp_unslash($_POST[$param]));
        } else {
            return $default;
        }
    }


    /**
     * Update settings
     *
     *
     * @since  1.0.0
     */
    public static function update($settings)
    {
        return update_option('notifia_settings', $settings);
    }


    /**
     * Get settings
     *
     * @since 1.0.0
     */
    public static function notifia_settings()
    {
        return get_option('notifia_settings');
    }


    /**
     * check if user is logged in
     *      * @since  1.0.0
     */
    public static function check_redirect()
    {
        $action = self::get('page');

        if ($action === self::$actions['index']['slug'] && empty(self::$settings['script_id'])) {
            wp_safe_redirect(admin_url('admin.php?page=' . self::$actions['guest']['sign-up']['slug']));
        }

    }


    /**
     * Add plugin link in sidebar
     * @since  1.0.0
     */
    public function admin_menu_add()
    {

        add_menu_page(
            self::$actions['index']['title'],
            self::$actions['index']['name'],
            'manage_options',
            self::$actions['index']['slug'],
            array(__CLASS__, self::$actions['index']['function']),
            plugin_dir_url(__FILE__) . '/images/logo32x32.svg');
    }


    /**
     * add sub menus
     * @since  1.0.0
     */
    public function admin_sub_menu_add()
    {
        $type = 'auth';

        if (empty(self::$settings['script_id'])) {
            $type = 'guest';
        }


        foreach (self::$actions[$type] as $key => $action) {
            add_submenu_page(
                self::$actions['index']['slug'],
                $action['title'],
                $action['name'], 'manage_options',
                $action['slug'],
                array(__CLASS__, $action['function'])
            );
        }

    }


    /**
     * Main Page Action
     */
    public static function action_admin_menu_page()
    {
        self::render_template('index',
            array(
                'dashboardPath' => self::$dashboardPath
            ));
    }


    /**
     * Sign Up Page Action
     */
    public static function action_admin_menu_sign_up()
    {
        $options = self::$settings;
        $options['googleLoginLink'] = self::$googleLoginLink;
        $options['fetchEndpoint'] = self::$fetchEndpoint;
        $options['success_action'] = 'notifia_post_sign_in';

        $options['loginPath'] = self::$loginPath;
        $options['signupPath'] = self::$signupPath;

        self::render_template('sign-up',
            array(
                'sign_in_link' => admin_url('admin.php?page=' . self::$actions['guest']['sign-in']['slug']),
                'options' => $options,
            ));
    }


    /**
     * Sign In Page Action
     */
    public static function action_admin_menu_sign_in()
    {

        $options = self::$settings;
        $options['success_action'] = 'notifia_post_sign_in';
        $options['form_type'] = 'sign-in';
        $options['endpoint'] = self::$loginEndpoint;
        $options['fetchEndpoint'] = self::$fetchEndpoint;
        $options['googleLoginLink'] = self::$googleLoginLink;
        $options['loginPath'] = self::$loginPath;
        self::render_template('sign-in',
            array(
                'sign_up_link' => admin_url('admin.php?page=' . self::$actions['guest']['sign-up']['slug']),
                'options' => $options,
            ));

    }


    /**
     * Sign out action
     */
    public static function action_admin_menu_sign_out()
    {
        $options = self::$settings;
        $options['action'] = 'notifia_post_clear_api_key';

        self::render_template(
            'sign-out', array(
                'options' => $options,
            )
        );
    }


    public function notifia_post_sign_in()
    {
        if (self::post('script_id')) {
            self::$settings['script_id'] = self::post('script_id');
            self::update(self::$settings);

            echo wp_json_encode(
                array(
                    'redirect_link' => admin_url('admin.php?page=' . self::$actions['index']['slug']),
                )
            );
            wp_die();
        }
    }

    /**
     * Processing sign out form
     */
    public function notifia_post_clear_api_key()
    {
        if (self::post('notifia_clear_api_key')) {
            self::$settings['script_id'] = null;
            echo wp_json_encode(
                array(
                    'error' => !self::update(self::$settings),
                    'redirect_link' => admin_url('admin.php?page=' . self::$actions['guest']['sign-in']['slug']),
                )
            );
            wp_die();
        }
    }


}
