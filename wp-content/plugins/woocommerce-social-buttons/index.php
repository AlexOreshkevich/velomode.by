<?php
/* Plugin Name: WooCommerce Social Buttons
 * Plugin URI: http://www.vivacityinfotech.net 
 * Description: A simple plugin to add most popular social like+share buttons to your Woocommerce store products and you can use shortcode [woocommerce_social_buttons]. 
 * Version: 1.0.1
 * Author: Vivacity Infotech Pvt. Ltd.
 * Author URI: http://www.vivacityinfotech.net
  Text Domain: woocommerce-social-buttons
  Domain Path: /languages/
 */
/*
  Copyright 2014  Vivacity InfoTech Pvt. Ltd.  (email : support@vivacityinfotech.net)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?php

function va_wc_sh_li_reg_like_settings() {
    register_setting('va_wc_share_like', 'va_share_like_fb');
    register_setting('va_wc_share_like', 'va_share_like_gp');
    register_setting('va_wc_share_like', 'va_share_like_li');
    register_setting('va_wc_share_like', 'va_share_like_tw');
    register_setting('va_wc_share_like', 'va_share_like_ic_size');
    register_setting('va_wc_share_like', 'va_share_prod_detail');
    register_setting('va_wc_share_like', 'va_share_spost_page');
}

if (is_admin()) {
    add_action('admin_init', 'va_wc_sh_li_reg_like_settings');
}
add_option('va_share_like_fb', 'true');
add_option('va_share_like_gp', 'true');
add_option('va_share_like_li', 'true');
add_option('va_share_like_tw', 'true');
add_option('va_share_like_ic_size', 'horizontal');
add_option('va_share_prod_detail', 'true');
add_option('va_share_spost_page', 'true');

function va_wc_sh_form_code() {
    global $post;
    $social_val = '';
    if (get_option('va_share_like_ic_size') == 'horizontal') {
        if (get_option('va_share_like_li') == 'true') {
            $social_val.='<span><script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script><div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="small" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir" data-yashareTheme="counter"></div></span>';
        }
    }
    if (get_option('va_share_like_ic_size') == 'vertical') {
        if (get_option('va_share_like_fb') == 'true') {
            $social_val.='<div><span class="fb-share-button" data-href="' . get_permalink($post->ID) . '" data-layout="box_count"></span>';
        }
        if (get_option('va_share_like_gp') == 'true') {
            $social_val.='<span class="g-plus" data-action="share" data-annotation="vertical-bubble" data-href="' . get_permalink($post->ID) . '"></span>';
        }
        if (get_option('va_share_like_li') == 'true') {
            $social_val.='<span><script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
                          <script type="IN/Share" data-counter="top"></script></span>';
        }
        if (get_option('va_share_like_tw') == 'true') {
            $social_val.='<span><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical">Tweet</a></span>';
        }
    }
    echo $social_val;
}


function va_wc_sh_form_short_code() {
    global $post;
    $social_val = '';
    
        if (get_option('va_share_like_fb') == 'true') {
            $social_val.='<span class="fb-share-button" data-href="' . get_permalink($post->ID) . '" data-layout="button_count"></span>';
        }
        if (get_option('va_share_like_gp') == 'true') {
            $social_val.='<span class="g-plus" data-action="share" data-annotation="bubble" data-href="' . get_permalink($post->ID) . '"></span>';
        }
        if (get_option('va_share_like_li') == 'true') {
            $social_val.='<span><script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
                          <script type="IN/Share" data-counter="right"></script></span>';
        }
        if (get_option('va_share_like_tw') == 'true') {
            $social_val.='<span><a href="https://twitter.com/share" class="twitter-share-button">Tweet</a></span></div>';
        }
   
    
    echo $social_val;
}


add_shortcode('woocommerce_social_buttons', 'va_wc_sh_form_short_code');
add_action('woocommerce_single_product_summary', 'va_wc_sh_form_code', 31);
add_action('admin_menu', 'va_wc_sh_social_menu');
add_action('wp_footer', 'va_wc_sh_social_footer');
function va_wc_sh_social_footer() {
    echo "<style type='text/css'>.fb_iframe_widget > span {display: table !important;}</style>";
}

function va_wc_sh_social_menu() {
    add_menu_page('Woocommerce Social Page', 'Share Panel', 'manage_options', 'Woocommerce-social-plugin', 'va_wc_sh_social_facebook_init', plugins_url('/images/fb_16.png', __FILE__));
}

function va_wc_sh_social_facebook_init() {
    $submit_val = 0;
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Submit'])) {

        $share_like_fb = sanitize_text_field($_REQUEST['share_like_fb']);
        $share_like_gp = sanitize_text_field($_REQUEST['share_like_gp']);
        $share_like_li = sanitize_text_field($_REQUEST['share_like_li']);
        $share_like_tw = sanitize_text_field($_REQUEST['share_like_tw']);
        $share_like_icsize = sanitize_text_field($_REQUEST['share_like_ic_size']);
        $share_prod_detail = sanitize_text_field($_REQUEST['share_prod_detail']);
        $share_spostpage = sanitize_text_field($_REQUEST['share_spost_page']);
        update_option('va_share_like_fb', $share_like_fb);
        update_option('va_share_like_gp', $share_like_gp);
        update_option('va_share_like_li', $share_like_li);
        update_option('va_share_like_tw', $share_like_tw);
        update_option('va_share_like_ic_size', $share_like_icsize);
        update_option('va_share_prod_detail', $share_prod_detail);
        update_option('va_share_spost_page', $share_spostpage);
        $submit_val = 1;
    }
    ?>
    <style type="text/css">
        .main_div{}
        .container_div{ }
        .button1 {background: #1E6CBE;color: #fff;border: 0 none; cursor: pointer;}
        .button1:hover {background:#555555;}
        .submitform {margin: 20px 0;}
        .box{
            padding: 20px;
            display: none;
            margin-top: 20px;

        }
        .share_form_table{font-size: 14px;}
        .form_table_tr_th{font-size:14px; color:#1E6CBE;font-weight:bold;width: 500px;
                          margin:15px 0px;padding:10px; background:#ffffff;border-left: 4px solid #1E6CBE; text-align: left;}
        .share_plugin_head{font-size:18px; color:#1E6CBE;font-weight:bold;text-transform: uppercase;width: 98%;
                           margin:15px 0px;padding:10px; background:#ffffff;border-left: 4px solid #1E6CBE; text-align:center;}

    </style>
    <div class="main_div">   
        <div><h3 class="share_plugin_head"><?php _e("Soical Share Setting Panel", "woocommerce-social-buttons"); ?></h3></div>
        <?php if (isset($submit_val) && $submit_val == 1) { ?>
            <div id="setting-error-settings_updated" class="updated settings-error"> 
                <p><strong>Settings saved.</strong></p></div>
        <?php } ?>
        <div class="container_div" >
            <form name="social" method="post" >   
                <table class="share_form_table" >
                    <tr><th>
                    <h3 class="form_table_tr_th">
                        <?php _e("Dispaly social icon Setting Panel", "woocommerce-social-buttons"); ?></h3></th></tr>
                    <tr valign="top">
                        <td><fieldset>
                                <label><input type="checkbox" name="share_like_fb" value="true" <?php echo (get_option('va_share_like_fb') == 'true' ? 'checked' : ''); ?>  /><span style="padding-left: 5px;font-weight: bold;"><?php _e('Facbook', 'woocommerce-social-buttons'); ?></span></label>
                                <br>
                                <label><input type="checkbox" name="share_like_gp" value="true" <?php echo (get_option('va_share_like_gp') == 'true' ? 'checked' : ''); ?>   /><span style="padding-left: 5px;font-weight: bold;"><?php _e('Google Plus', 'woocommerce-social-buttons'); ?></span></label>
                                <br>
                                <label> <input type="checkbox" name="share_like_li"  value="true" <?php echo (get_option('va_share_like_li') == 'true' ? 'checked' : ''); ?>  /><span style="padding-left: 5px;font-weight: bold;"><?php _e('Linkedin', 'woocommerce-social-buttons'); ?></span></label>
                                <br>
                                <label> <input type="checkbox" name="share_like_tw" value="true" <?php echo (get_option('va_share_like_tw') == 'true' ? 'checked' : ''); ?>  ><span style="padding-left: 5px;font-weight: bold;"><?php _e('Twitter', 'woocommerce-social-buttons'); ?></span></label>

                            </fieldset></td>
                    </tr>
                    <tr><th scope="row">
                    <h3 class="form_table_tr_th" >
                        <?php _e("Icon Size Setting Panel", "woocommerce-social-buttons"); ?></h3></th></tr>  
                    <tr valign="top">
                        <td><fieldset>
                                <label><input type="radio" name="share_like_ic_size" value="vertical" <?php echo (get_option('va_share_like_ic_size') == 'vertical' ? 'checked' : ''); ?> /> <span style="padding-left: 5px;font-weight: bold;"><?php _e('Vertical', 'woocommerce-social-buttons'); ?></span></label>
                                <br>
                                <label><input type="radio" name="share_like_ic_size" value="horizontal" <?php echo (get_option('va_share_like_ic_size') == 'horizontal' ? 'checked' : ''); ?> /><span style="padding-left: 5px;font-weight: bold;"> <?php _e('Horizontal', 'woocommerce-social-buttons'); ?></span></label>
                            </fieldset></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="large box">You have selected <strong>Large Icon</strong> <?php echo '<img src="' . plugins_url('images/large.png', __FILE__) . '" > '; ?></div>
                            <div class="small box">You have selected <strong>Small Icon</strong><?php echo '<img src="' . plugins_url('images/small.png', __FILE__) . '" > '; ?></div>
                            <div class="medium box">You have selected <strong>medium Icon</strong> <?php echo '<img src="' . plugins_url('images/medium.png', __FILE__) . '" > '; ?></div>
                        </td>
                    </tr>
                    <tr><th scope="row">
                    <h3 class="form_table_tr_th" >
                        <?php _e("Position Settings Panel", "woocommerce-social-buttons"); ?></h3></th></tr>
                    <tr valign="top">
                        <td><fieldset>
                                <label><input type="checkbox" name="share_spost_page" value="true"  <?php echo (get_option('va_share_spost_page') == 'true' ? 'checked' : ''); ?> /> <span style="padding-left: 5px;font-weight: bold;"><?php _e('Show on Post', 'woocommerce-social-buttons'); ?></span></label>
                                <br>
                                <label> <input type="checkbox" name="share_prod_detail" value="true" <?php echo (get_option('va_share_prod_detail') == 'true' ? 'checked' : ''); ?> /><span style="padding-left: 5px;font-weight: bold;"><?php _e('Show on Product Details Page', 'woocommerce-social-buttons'); ?></span></label>

                            </fieldset></td>
                    </tr>
                </table>
                <input type="hidden" name="action" value="update" />
                <div  class="submitform">
                    <input type="submit" name="Submit" class="button1" value="Save Changes">
                </div>
            </form>
        </div>
    </div>
    <?php
}

function va_wc_sh_my_enqueue() {
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . 'script_file.js', 'false');
}

add_action('wp_print_scripts', 'va_wc_sh_my_enqueue');
?>
