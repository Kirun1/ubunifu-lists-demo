<?php
function kwsldac_ubunifuList_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'kwsldac'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2>
                <?php _e('UbunifuList', 'kwsldac')?> 
                <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=ubunifuList_form');?>">
                    <?php _e('Add new', 'kwsldac')?>
                </a>
            </h2>
            <?php echo $message; ?>

            <form id="ubunifuList-table" method="POST">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <?php $table->display() ?>
            </form>

        </div>
    <?php
}


function kwsldac_ubunifuList_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cte'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'lastname'  => '',
        'email'     => '',
        'phone'     => null,
        'company'   => '',
        'web'       => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = kwsldac_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'kwsldac');
                } else {
                    $notice = __('There was an error while saving item', 'kwsldac');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'kwsldac');
                } else {
                    $notice = __('There was an error while updating item', 'kwsldac');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'kwsldac');
            }
        }
    }

    
    add_meta_box('ubunifuList_form_meta_box', __('UbunifuList data', 'kwsldac'), 'kwsldac_ubunifuList_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php _e('UbunifuList', 'kwsldac')?> 
                <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=ubunifuList');?>">
                    <?php _e('back to list', 'kwsldac')?>
                </a>
            </h2>

            <?php if (!empty($notice)): ?>
                <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
                <div id="message" class="updated"><p><?php echo $message ?></p></div>
            <?php endif;?>

            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                    
                            <?php do_meta_boxes('contact', 'normal', $item); ?>
                            <input type="submit" value="<?php _e('Save', 'kwsldac')?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php
}

function kwsldac_ubunifuList_form_meta_box_handler($item)
{
    ?>
    <tbody >
            
        <div class="formdatabc">		
            
            <form >
                <div class="form2bc">
                    <p>			
                        <label for="name"><?php _e('Name:', 'kwsldac')?></label>
                        <br>	
                        <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>" required>
                    </p>
                    <p>	
                        <label for="lastname"><?php _e('Last Name:', 'kwsldac')?></label>
                        <br>
                        <input id="lastname" name="lastname" type="text" value="<?php echo esc_attr($item['lastname'])?>" required>
                    </p>
                </div>	
                <div class="form2bc">
                    <p>
                        <label for="email"><?php _e('E-Mail:', 'kwsldac')?></label> 
                        <br>	
                        <input id="email" name="email" type="email" value="<?php echo esc_attr($item['email'])?>" required>
                    </p>
                    <p>	  
                        <label for="phone"><?php _e('Phone:', 'kwsldac')?></label> 
                        <br>
                        <input id="phone" name="phone" type="tel" value="<?php echo esc_attr($item['phone'])?>">
                    </p>
                </div>
                <div class="form2bc">
                    <p>
                        <label for="company"><?php _e('Company:', 'kwsldac')?></label> 
                        <br>	
                        <input id="company" name="company" type="text" value="<?php echo esc_attr($item['company'])?>">
                    </p>
                    <p>	  
                        <label for="web"><?php _e('Web:', 'kwsldac')?></label> 
                        <br>
                        <input id="web" name="web" type="text" value="<?php echo esc_attr($item['web'])?>">
                    </p>
                </div>					
            </form>
        </div>
    </tbody>
<?php
}

add_shortcode( 'mylistdemo', 'kwsldac_ubunifuList_fron_end_display' );
function kwsldac_ubunifuList_fron_end_display() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'cte';

    $lysts = $wpdb->get_results( "SELECT * FROM $table_name" );
    ?>
    <h3 style="text-align: center;">My List Demo</h3>
    <?php

    foreach ( $lysts as $lyst ) {
        ?>
            <ul id="sortable" class="my-list">
                <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php echo $lyst->name; ?>, <?php echo $lyst->lastname; ?>, <?php echo $lyst->email; ?>, <?php echo $lyst->phone; ?>, <?php echo $lyst->company; ?>, <?php echo $lyst->web; ?></li>
            </ul>

        <?php

    }

}