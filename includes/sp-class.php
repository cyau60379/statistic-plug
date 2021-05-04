<?php

define('PATH', dirname(__DIR__) . '/includes/');

class StatisticPlug {

    const SELECT = "SELECT DISTINCT slct FROM tbl";

    /**
     * StatisticPlug constructor.
     */
    public function __construct() {
        register_activation_hook(__FILE__, 'sp_create_table_if_not_exists');
        add_action('init', array($this, 'sp_register_connection'));
        // add admin menu
        add_action('admin_menu', array($this, 'sp_admin_menu'));
    }

    /**
     * Counter of the number of visits of the website
     */
    function sp_count_visits() {
        global $wpdb;
        $query   = str_replace(array("slct", "tbl"), array("count(session)", $wpdb->prefix . "visit"), self::SELECT);
        $counter = $wpdb->get_var($query);
        echo $counter;
    }

    function sp_chart_data() {
        global $wpdb;
        $query  = str_replace(
                      array("slct", "tbl"),
                      array("count(session) AS count, date", $wpdb->prefix . "visit"),
                      self::SELECT
                  ) . " GROUP BY date";
        $points = $wpdb->get_results($query);
        $data   = array();
        foreach ($points as $point) {
            array_push($data, array('y' => intval($point->count), "label" => $point->date));
        }

        return $data;
    }

    function sp_create_table_if_not_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . "visit";
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id int NOT NULL AUTO_INCREMENT,
                session VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
				UNIQUE KEY id (id)
                );";
            //reference to upgrade.php file
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    function sp_register($session) {
        global $wpdb;
        $table_name = $wpdb->prefix . "visit";
        $date       = date("Y-m-d");
        $wpdb->query(
            $wpdb->prepare("INSERT INTO $table_name(session, date) VALUES (%s, %s)", $session, $date)
        );
    }

    function sp_admin_menu() {
        add_menu_page(
            'Statistics SW', // Page Title
            'Statistics SW', // Menu Link
            'manage_options', // Capability requirement to see the link
            PATH . 'sp-counter-page.php' // The 'slug' - file to display when clicking the link
        );
    }

    function sp_register_connection() {
        global $wpdb;
        $cookie = $_COOKIE['PHPSESSID'];
        if (!empty($cookie)) {
            $this->sp_create_table_if_not_exists();
            $query   = str_replace(array("slct", "tbl"), array("session", $wpdb->prefix . "visit"), self::SELECT) . " WHERE session = %s";
            $results = $wpdb->query(
                $wpdb->prepare($query, $cookie)
            );
            if (empty($results)) {
                $this->sp_register($cookie);
            }
        }
    }
}