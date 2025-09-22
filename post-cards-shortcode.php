<?php
/**
 * Plugin Name: Post Cards Shortcode
 * Description: Display posts in a card design with featured image, title, date, and Read More button. Includes category filter, skip option, and load more functionality.
 * Version: 1.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Shortcode [post_cards category="slug" posts_per_page="6" skip="3"]
function pcs_post_cards_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'posts_per_page' => 6,
        'skip' => 0,
    ), $atts, 'post_cards');

    ob_start();

    echo '<div id="pcs-card-wrapper" 
            data-category="'.esc_attr($atts['category']).'" 
            data-posts-per-page="'.intval($atts['posts_per_page']).'" 
            data-skip="'.intval($atts['skip']).'" 
            data-page="1">';

    pcs_render_posts($atts['category'], 1, $atts['posts_per_page'], $atts['skip']);

    echo '</div>';

    echo '<div class="pcs-load-more-wrap">
            <button id="pcs-load-more">Load More</button>
          </div>';

    return ob_get_clean();
}
add_shortcode('post_cards', 'pcs_post_cards_shortcode');

// Render posts
function pcs_render_posts($category, $paged, $ppp, $skip = 0) {
    $offset = $skip + ($paged - 1) * $ppp;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $ppp,
        'offset' => $offset,
        'post_status' => 'publish',
    );

    if (!empty($category)) {
        $args['category_name'] = sanitize_text_field($category);
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post(); ?>
            <div class="pcs-card">
                <div class="pcs-card-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('medium');
                        } else {
                            echo '<img src="' . esc_url(get_template_directory_uri() . '/no-image.jpg') . '" alt="No image">';
                        } ?>
                    </a>
                </div>
                <div class="pcs-card-content">
                    <h3 class="pcs-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="pcs-card-date"><?php echo get_the_date(); ?></p>
                    <a href="<?php the_permalink(); ?>" class="pcs-read-more">Read More</a>
                </div>
            </div>
        <?php }
        wp_reset_postdata();
    } else {
        echo '<p>No more posts found.</p>';
    }
}

// AJAX Load More
function pcs_load_more_ajax() {
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $ppp   = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 6;
    $cat   = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $skip  = isset($_POST['skip']) ? intval($_POST['skip']) : 0;

    pcs_render_posts($cat, $paged, $ppp, $skip);

    wp_die();
}
add_action('wp_ajax_pcs_load_more', 'pcs_load_more_ajax');
add_action('wp_ajax_nopriv_pcs_load_more', 'pcs_load_more_ajax');

// Enqueue Scripts + CSS
function pcs_enqueue_assets() {
    wp_enqueue_script('pcs-script', plugin_dir_url(__FILE__) . 'pcs-script.js', array('jquery'), '1.3', true);
    wp_localize_script('pcs-script', 'pcs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    wp_enqueue_style('pcs-style', plugin_dir_url(__FILE__) . 'pcs-style.css', array(), '1.3');
}
add_action('wp_enqueue_scripts', 'pcs_enqueue_assets');
