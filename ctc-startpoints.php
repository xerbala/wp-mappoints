<?php
/*
Plugin Name: CTC Start Points
Plugin URI: http://www.readingctc.co.uk/
Description: Display location data on a Google map
Author: Al Neal
Version: 0.1
Author URI: http://www.readingctc.co.uk/
License: GPLv3
*/
/* Inserts a Google map with markers and info boxes
 * Inserts at the start of 'the_content' hook
 * Inserts on pages indicated by meta box checkbox
 * Data driven from mysql table 'startpoints'
 * Data manually manipulated in this table. No functionallity provided
 * Genearl query:
 * SELECT 
 *  id,                 // autoincrement int
 *  divid,              // unused - char(10)
 *  title,              // Title of map marker
 *  lat,                // Latitude of marker 
 *  lon,                // Longitude of marker
 *  grouping,           // Very important. Defines the set of points to appear on the map
 *  startcentre,        // S - Start point i.e. a marker on the map; C - Centre point of the map 
 *  description         // Text describing the marker. Appears in Info Box
 * FROM
 *  startpoints
 *
 * The meta box only appears for administrators
 *
 *
 */



if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'RctcStartPoints' ) ) :

class RctcStartPoints {

    public static function init() {
        $rctcstartpoints = new self();
    }

    public function __construct() {
        $this->includes();
        $this->setup_custom_post();
        add_action('wp_enqueue_scripts',array($this,'register_rctc_plugin_styles'));
        $this->setup_metabox();
        $this->setup_google_maps();
        $this->setup_ajax();
    }

    private function includes() {
        define('RCTC_PATH', plugin_dir_path(__FILE__));
        require_once(RCTC_PATH . 'console_output.php');
    }

    private function setup_custom_post() {
        add_action('the_post',array($this,'sp_load_filter'));
    }

    private function setup_metabox() {
        if (current_user_can('administrator')) { // only display for administrators
            add_action('add_meta_boxes',array($this,'sp_meta_box_add'));
            add_action('save_post',array($this,'sp_meta_box_save'));
        }
    }

    private function setup_google_maps() {
        add_action('wp_enqueue_scripts',array($this,'rctc_load_google_maps') );
    }

    private function setup_ajax() {
        add_action('wp_enqueue_scripts', array($this,'rctcsp_js_scripts'));
        add_action('wp_ajax_rctcsp_handler', array($this,'rctcsp_handler_func'));
        add_action('wp_ajax_nopriv_rctcsp_handler',array($this,'rctcsp_handler_func'));
    }

    /* ------------------ end of constuctor functions --------------------------- */

    public function rctc_load_google_maps() {
    // Add Google map api stuff
    // Insert your own Google API key: key=###########################
	    wp_enqueue_script('rctc-google-maps','https://maps.googleapis.com/maps/api/js?v=3.exp&key=############################',array());
    }

    public function sp_load_filter() {
        global $post;
        $values = get_post_custom($post->ID  );
        
        if( isset( $values['sp_check'] ) && $values['sp_check'][0] == 'on'  ) {
            add_filter('the_content',array($this,'write_html_frame') );
        }
    }

    public function sp_meta_box_add() {
        add_meta_box('sp-meta-id','Settings for Startpoints Map',array($this,'sp_meta_box_cb'),'page','normal','default');
    }
    public function sp_meta_box_cb() {
        global $wpdb;
        $wpdb->show_errors();
        $result = $wpdb->get_results("SELECT DISTINCT grouping FROM startpoints ORDER BY grouping" );

        global $post;
        $values = get_post_custom( $post->ID );
        $selected_group = isset($values['sp_group_select']) ? esc_attr($values['sp_group_select'][0]) : '';
        $selected_zoom =  isset($values['sp_zoom_select'])  ? esc_attr($values['sp_zoom_select'][0])  : '' ;
        $check = isset($values['sp_check'])  ? esc_attr($values['sp_check'][0])  : '' ;

        wp_nonce_field('sp_meta_box_nonce','meta_box_nonce');
    ?>
        <p>
            <label for="sp_group_select">Group of Start Points</label>
            <select name="sp_group_select" id="sp_group_select">
<?php foreach($result as $grp) { ?>
<option value=<?php echo '"'.$grp->grouping.'"'; selected( $selected_group, 'RDG-WED' );  echo ' >'.$grp->grouping; ?></option>
<?php } ?>
            </select>
        </p>

        <p>
            <label for="sp_zoom_select">Map Zoom Level</label>
            <select name="sp_zoom_select" id="sp_zoom_select">
                <option value="8" <?php selected( $selected_zoom, '8' ); ?> > 8</option>
                <option value="9" <?php selected( $selected_zoom, '9' ); ?> > 9</option>
                <option value="10" <?php selected( $selected_zoom, '10' ); ?> >10</option>
                <option value="11" <?php selected( $selected_zoom, '11' ); ?> >11</option>
                <option value="12" <?php selected( $selected_zoom, '12' ); ?> >12</option>
                <option value="13" <?php selected( $selected_zoom, '13' ); ?> >13</option>
                <option value="14" <?php selected( $selected_zoom, '14' ); ?> >14</option>
                <option value="15" <?php selected( $selected_zoom, '15' ); ?> >15</option>
            </select>
        <p>
            <input type="checkbox" id="sp_check" name="sp_check" <?php checked( $check, 'on'); ?> />
            <label for="sp_check">Check to include map on page</label>
        </p>

    <?php
        $wpdb->flush($result);
    }
    
    public function sp_meta_box_save( $post_id ) {
        // check if autosaving and get out
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

        // check if nonce is there and is can be verified else get out
        if( !isset( $_POST['meta_box_nonce']) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'sp_meta_box_nonce' ) ) return;

        // check if user can edit posts
        if( !current_user_can('edit_posts') ) return;

        if( isset($_POST['sp_group_select']) )
            update_post_meta($post_id, 'sp_group_select', esc_attr( $_POST['sp_group_select'] ) );
    
        if( isset($_POST['sp_zoom_select']) )
            update_post_meta($post_id, 'sp_zoom_select', esc_attr( $_POST['sp_zoom_select'] ) );

        $chk = isset($_POST['sp_check']) && $_POST['sp_group_select'] ? 'on' : 'off';
        update_post_meta( $post_id, 'sp_check', $chk );
    }


    public function write_html_frame( $content ) {
        $out[] = '<div id="sp-wrapper">'; 
        $out[] = '<div id="sp-main">';
        $out[] = '<div id="map-canvas"></div>';
        $out[] = '</div>';
        $out[] = '<div id="sp-footer"></div>';
        $out[] = '</div>';
        $out[] = $content;

        return implode($out);
    }

    public function register_rctc_plugin_styles() {
        wp_register_style('rctc-sp-style', plugins_url('/css/ctc-startpoints.css', __FILE__));
        wp_enqueue_style('rctc-sp-style');
    }

    public function rctcsp_js_scripts() { 
        wp_enqueue_script('rctcsp-script', plugins_url('/js/rctc_sp.js', __FILE__ ), array('jquery') );
        global $post;
        $values = get_post_custom( $post->ID );
        $selected_group = isset($values['sp_group_select']) ? esc_attr($values['sp_group_select'][0]) : '';
        $selected_zoom = isset($values['sp_zoom_select']) ? esc_attr($values['sp_zoom_select'][0]) : '';
        wp_localize_script( 'rctcsp-script','rctcajax',
            array('ajax_url' => admin_url('admin-ajax.php'),
            'sp_grouping' => $selected_group,
            'sp_zoom' => $selected_zoom,
            'sp_plugin_dir' => plugins_url('/', __FILE__)  ) );
    }

    public function rctcsp_handler_func() {
        global $wpdb;
        $grouping = $_POST['spgroup'];
        $wpdb->show_errors();
        header("Content-Type: application/json");

        $result = $wpdb->get_results($wpdb->prepare("SELECT id, divid, title, lat, lon, grouping, startcentre, description FROM startpoints WHERE grouping = %s", $grouping ));
        if (! isset($response)) $response = new stdClass();
        $i = 0;
        foreach($result as $row)
        {

            if ($row->startcentre == 'C') {
            $centre = array(
                    'id'          => $row->id,
                    'divid'       => $row->divid,
                    'title'       => $row->title,
                    'lat'         => $row->lat,
                    'lon'         => $row->lon,
                    'grouping'    => $row->grouping,
                    'startcentre' => $row->startcentre,
                    'description' => stripslashes($row->description)
                ); 
            } else {
            $response->start[$i] = array(
                    'id'          => $row->id,
                    'divid'       => $row->divid,
                    'title'       => $row->title,
                    'lat'         => $row->lat,
                    'lon'         => $row->lon,
                    'grouping'    => $row->grouping,
                    'startcentre' => $row->startcentre,
                    'description' => stripslashes($row->description)
                );
            $i++;
            }
        }
        $response->centre = $centre;
        $response->rows = $wpdb->num_rows;
        $response->grouping = $grouping;
        $response->zoom = $_POST['spzoom'];
        $response->pluginurl = $_POST['spurl'];

        $wpdb->flush($result);
        wp_die(json_encode($response));
    }

}


endif;

add_action('plugins_loaded', array( 'RctcStartPoints', 'init'), 10);

// End of File
