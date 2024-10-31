<?php
/*
 *  Plugin Name: Product Explode Plugin
 *  Description: This plugin allows you to explode the image of your Group product.
 *  Version: 2.0
 *  Author: wamasoftware
 *  Author URI: http://www.wamasoftware.com/
 */

add_action('init', 'wspewp_loadScripts');
function wspewp_loadScripts()
{
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        ?>
        <div class="error">
            <p><strong>Product Explode</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
        <?php
    }
    if (!in_array('wpc-grouped-product/wpc-grouped-product.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        ?>
        <div class="error">
            <p><strong>Product Explode</strong> requires WPC Grouped Product Plugin.</p>
        </div>
        <?php
    }


    if (is_admin()) {
        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            // Check if the post type matches your specific post type
            $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : get_post_type($_GET['post']);

            if ($post_type == 'product') {
                wp_enqueue_script('jQuery');
                wp_register_script('dataTable', plugin_dir_url(__FILE__) . 'js/dataTables.min.js', array());
                wp_register_style('dataTable', plugin_dir_url(__FILE__) . 'css/dataTables.min.css', array());
                wp_enqueue_script('dataTable');
                wp_enqueue_style('dataTable');

                wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'js/script.js');
                wp_localize_script('jQuery', 'script', array('ajaxurl' => admin_url('admin-ajax.php')));

                wp_register_script('jqueryUIjs', plugin_dir_url(__FILE__) . 'js/jquery-ui.min.js', array());
                wp_register_style('jqueryUIcss', plugin_dir_url(__FILE__) . 'css/jquery-ui.min.css', array());
                wp_enqueue_script('jqueryUIjs');
                wp_enqueue_style('jqueryUIcss');
            }
        }
    }
    wp_enqueue_script('scrollTo', plugin_dir_url(__FILE__) . 'js/jquery.scrollTo.min.js');
    wp_localize_script('scrollTo', 'scrollto_script', array());
    wp_register_style('style', plugin_dir_url(__FILE__) . 'css/style.css', array());

    wp_enqueue_style('style');

}


add_filter('woocommerce_product_data_tabs', 'add_custom_wspewp_tab', 99, 1);

// Adding Product Explode tab on products menu in Admin
function add_custom_wspewp_tab($productTabs)
{
    $productTabs['product-explode'] = array(
        'label' => __('Product Explode', 'my_text_domain'),
        'target' => 'my_custom_product_data',
        'class' => array('show_if_grouped'),
    );

    return $productTabs;
}

add_action('woocommerce_product_data_panels', 'add_custom_wspewp_fields');
function add_custom_wspewp_fields()
{
    global $post;
    $productDetail = wc_get_product(get_the_ID());
    $childProducts = $productDetail->get_children();
    $productID = $productDetail->get_id();
    ?>

    <!--Displaying product image on product explode tab-->
    <div id="my_custom_product_data" class="panel woocommerce_options_panel">
        <?php
        $productMetaData = get_post_meta($post->ID);
        // print_r($productMetaData);exit;
        $imgUrl = "";
        if (!empty($productMetaData) && isset($productMetaData['_thumbnail_id'])) {
            $imgUrl = wp_get_attachment_url($productMetaData['_thumbnail_id'][0]);
        } else {
            ?>
            <div class="error1" style="color:red; font-weight: 700;">
                <p><strong>Add Featured Image of Product to Explode Product.</p>
            </div>
            <?php
        }
        ?>
        <div id="test" class="exploded-area">
            <img src="<?php echo $imgUrl ?>">

            <?php for ($i = 1; $i <= count($childProducts); $i++) {
                $labelWidth = get_post_meta($productID, "pe_label_field_" . $i . "_width", true);
                $labelHeight = get_post_meta($productID, "pe_label_field_" . $i . "_height", true);

                $labelTop = get_post_meta($productID, "position_top_" . $i, true);
                $labelLeft = get_post_meta($productID, "position_left_" . $i, true);
                $labelText = get_post_meta($productID, "label_text_" . $i, true);
                ?>
                <div id="<?php echo "draggable_" . $i ?>" data-lableid="<?php echo $i; ?>"
                    class="ui-widget-content draggable draggable-label"
                    style="left: <?php echo $labelLeft . 'px' ?>; top: <?php echo $labelTop . 'px' ?>; width: <?php echo $labelWidth . 'px' ?>;height: <?php echo $labelHeight . 'px' ?>;">
                    <input type="hidden" name="position_top_<?php echo $i; ?>" value="<?php echo $labelTop; ?>"
                        class="top position_top_<?php echo $i; ?>">
                    <span name="label_text_<?php echo $i; ?>" value="<?php echo $labelText; ?>"
                        class="label-text label_text_<?php echo $i; ?>">
                        <?php echo $labelText; ?>
                    </span>
                    <?php echo $i; ?>
                    <input type="hidden" name="position_left_<?php echo $i; ?>" value="<?php echo $labelLeft; ?>"
                        class="left position_left_<?php echo $i; ?>">
                </div>
            <?php } ?>
        </div>
        <?php require_once('associative-product-list.php'); ?>
    </div>
    <?php
}

add_action('save_post', 'productExplodePostMeta', 10, 3);
function productExplodePostMeta($productID)
{
    if (isset($_REQUEST['grouped_products'])) {
        for ($i = 1; $i <= count($_REQUEST['grouped_products']); $i++) {
            update_post_meta($productID, "pe_label_field_" . $i . "_width", $_POST['pe_label_field_' . $i . '_width']);
            update_post_meta($productID, "pe_label_field_" . $i . "_height", $_POST['pe_label_field_' . $i . '_height']);

            update_post_meta($productID, "position_top_" . $i, $_POST['position_top_' . $i]);
            update_post_meta($productID, "position_left_" . $i, $_POST['position_left_' . $i]);

            update_post_meta($productID, "label_text_" . $i, $_POST['label_text_' . $i]);
        }
    }
}

function wspewp_plugin_path()
{
    // gets the absolute path to this plugin directory

    return untrailingslashit(plugin_dir_path(__FILE__));
}

add_action('woocommerce_after_single_product_summary', 'wspewp_displayProductList');
function wspewp_displayProductList()
{
    global $wpdb, $product, $post, $i;

    $productDetails = wc_get_product(get_the_ID());
    $childProducts = $productDetails->get_children();
    $product_id = $productDetails->get_id();

    if (!empty($childProducts)) { ?>
        <form class="cart grouped_form"
            action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
            method="post" enctype="multiple/form-data">

            <!--    Displaying Image    -->
            <div id="my_custom_product_data" class="panel woocommerce_options_panel">
                <?php
                $productMeta = get_post_meta($post->ID);
                $explodeImgURL = "";
                if (!empty($productMeta) && isset($productMeta['_thumbnail_id'])) {
                    $explodeImgURL = wp_get_attachment_url($productMeta['_thumbnail_id'][0]);
                } else {
                    ?>
                    <div class="error1" style="color:red; font-weight: 700;">
                        <p><strong>Add Product Featured Image to Explode Product.</p>
                    </div>
                    <?php
                }
                ?>
                <img src="<?php echo $explodeImgURL ?>" width="100%">

                <?php for ($i = 1; $i <= count($childProducts); $i++) {
                    $labelWidth = get_post_meta($product_id, "pe_label_field_" . $i . "_width", true);
                    $labelHeight = get_post_meta($product_id, "pe_label_field_" . $i . "_height", true);

                    $labelTop = get_post_meta($product_id, "position_top_" . $i, true);
                    $labelLeft = get_post_meta($product_id, "position_left_" . $i, true);

                    $labelText = get_post_meta($product_id, "label_text_" . $i, true);
                    ?>
                    <div data-lableid="<?php echo $i; ?>" id="<?php echo "draggable_" . $i ?>"
                        class="ui-widget-content draggable draggable-label"
                        style="left: <?php echo $labelLeft . 'px' ?>; top: <?php echo $labelTop . 'px' ?>; width: <?php echo $labelWidth . 'px' ?>;height: <?php echo $labelHeight . 'px' ?>;">
                        <input type="hidden" name="position_top_<?php echo $i; ?>" value="<?php echo $labelTop; ?>"
                            class="position_top_<?php echo $i; ?>">
                        <span name="label_text_<?php echo $i; ?>" value="<?php echo $labelText; ?>"
                            class="label-text label_text_<?php echo $i; ?>">
                            <?php echo $labelText; ?>
                        </span>
                        <?php echo $i; ?>
                        <input type="hidden" name="position_left_<?php echo $i; ?>" value="<?php echo $labelLeft; ?>"
                            class="position_left_<?php echo $i; ?>">
                    </div>
                <?php }
                $i = 1;
                ?>
            </div>

            <!--    Displaying Products Table-->
            <div class="product_meta display" id="ass_pr_list_table">
                <table id="ass_prod_list" border="1">
                    <thead>
                        <tr>
                            <th>Product Image</th>
                            <th width="70px">SKU (Item No.)</th>
                            <th width="240px">Product Name</th>
                            <th width="100px">Price</th>
                            <th width="70px">Qty</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($childProducts as $key => $value) {
                            $productslist = get_post($value);
                            $getProduct = new WC_Product($value);
                            $postId = $productslist->ID;
                            $postName = $productslist->post_title;
                            $prodPrice = $getProduct->get_price_html();
                            $prodSku = $getProduct->get_sku();
                            $image = wp_get_attachment_image_src(get_post_thumbnail_id($postId), 'single-post-thumbnail');
                            ?>
                            <tr class="tr_ass_pr_list" id="tr_ass_pr_list_id_<?php echo $i; ?>"
                                name="tr_ass_pr_list_id_<?php echo $i; ?>">
                                <td><a target="_blank" href="<?php echo get_permalink($postId) ?>"><img width="75px"
                                            title="<?php echo $postName; ?>" height="75px"
                                            src="<?php echo $image ? $image[0] : wc_placeholder_img_src(); ?>"
                                            data-id="<?php echo $postId ?>"></a></td>
                                <td>
                                    <?php echo $prodSku; ?>
                                </td>
                                <td><a target="_blank" href="<?php echo get_permalink($postId) ?>">
                                        <?php echo $postName; ?>
                                </td>
                                <td>
                                    <?php echo $prodPrice; ?>
                                </td>
                                <td>
                                    <input type="number" id="quantity_5b3dac65c4092" class="input-text qty text" step="1" min="0"
                                        max="100" name="quantity[<?php echo $postId; ?>]" value="0" max title="Qty" size="4"
                                        pattern="[0-9]*" inputmode="numeric" aria-labelledby="">
                                </td>
                                <td class="text-center">
                                    <button type="submit" class="single_add_to_cart_button button alt">
                                        <?php echo esc_html($product->single_add_to_cart_text()); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        } ?>
                    </tbody>
                </table>
                <input type="hidden" name="add-to-cart" value="<?php echo $product_id; ?>" />
            </div>
        </form>
    <?php } ?>

    <script>
        jQuery(document).ready(function () {
            //jQuery(".draggable-label").resizable();
            jQuery('.draggable-label').on('click', function () {
                var rowid = jQuery(this).data('lableid');
                jQuery('.tr_ass_pr_list').siblings().removeClass("highlight");
                jQuery.scrollTo(jQuery('#tr_ass_pr_list_id_' + rowid).addClass("highlight"), 1000);
            });
        });
    </script>
    <?php
}

add_filter('woocommerce_locate_template', 'wspewp_woocommerce_locate_template', 10, 3);
function wspewp_woocommerce_locate_template($template, $template_name, $template_path)
{
    global $woocommerce;

    $_template = $template;

    if (!$template_path)
        $template_path = $woocommerce->template_url;

    $plugin_path = wspewp_plugin_path() . '/woocommerce/';

    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            $template_path . $template_name,
            $template_name
        )
    );

    // Modification: Get the template from this plugin, if it exists
    if (!$template && file_exists($plugin_path . $template_name))
        $template = $plugin_path . $template_name;

    // Use default template
    if (!$template)
        $template = $_template;

    // Return what we found
    return $template;
}