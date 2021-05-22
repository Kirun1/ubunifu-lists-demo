<?php
/**
* Plugin Name: Ubunifu Lists Demo
* Description: This plugin is to create Lists on pages.
* Version:     1.0
* Plugin URI: https://kirundabrian.ml
* Author:      Kirunda Brian
* Author URI:  https://kirundabrian.ml
* Text Domain: kwsldac
*/

defined( 'ABSPATH' ) or die( 'Get  out of here!!!!' );

require plugin_dir_path( __FILE__ ) . 'includes/metabox-p1.php';

function kwsldac_custom_admin_styles() {
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css');
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
    wp_enqueue_script('jquery-js', 'https://code.jquery.com/jquery-1.10.2.js');
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js');
    wp_enqueue_script('custom-js', plugins_url('/js/custom.js', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'kwsldac_custom_admin_styles');


function kwsldac_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'cte'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR (50) NOT NULL,
      lastname VARCHAR (100) NOT NULL,
      email VARCHAR(100) NOT NULL,
      phone VARCHAR(15) NULL,
      company VARCHAR(100) NULL,
      web VARCHAR(100) NULL,
      PRIMARY KEY  (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

register_activation_hook(__FILE__, 'kwsldac_install');



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'name',
            'plural'   => 'names',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=ubunifuList_form&id=%s">%s</a>', $item['id'], __('Edit', 'kwsldac')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'kwsldac')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Name', 'kwsldac'),
            'lastname'  => __('Last Name', 'kwsldac'),
            'email'     => __('E-Mail', 'kwsldac'),
            'phone'     => __('Phone', 'kwsldac'),
            'company'   => __('Company', 'kwsldac'),
            'web'       => __('Web', 'kwsldac'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'lastname'  => array('lastname', true),
            'email'     => array('email', true),
            'phone'     => array('phone', true),
            'company'   => array('company', true),
            'web'       => array('web', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cte'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cte'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'lastname';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function kwsldac_admin_menu()
{
    add_menu_page(__('UbunifuList', 'kwsldac'), __('UbunifuList', 'kwsldac'), 'activate_plugins', 'ubunifuList', 'kwsldac_ubunifuList_page_handler');
    add_submenu_page('ubunifuList', __('UbunifuList', 'kwsldac'), __('UbunifuList', 'kwsldac'), 'activate_plugins', 'ubunifuList', 'kwsldac_ubunifuList_page_handler');
   
    add_submenu_page('ubunifuList', __('Add new', 'kwsldac'), __('Add new', 'kwsldac'), 'activate_plugins', 'ubunifuList_form', 'kwsldac_ubunifuList_form_page_handler');
}

add_action('admin_menu', 'kwsldac_admin_menu');


function kwsldac_validate_contact($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'kwsldac');
    if (empty($item['lastname'])) $messages[] = __('Last Name is required', 'kwsldac');
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'kwsldac');
    if(!empty($item['phone']) && !absint(intval($item['phone'])))  $messages[] = __('Phone can not be less than zero');
    if(!empty($item['phone']) && !preg_match('/[0-9]+/', $item['phone'])) $messages[] = __('Phone must be number');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}