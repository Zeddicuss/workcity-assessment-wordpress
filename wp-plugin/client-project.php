<?php
/**
 * Plugin Name: Client Projects
 * Description: Adds a Client Project custom post type with meta fields and shortcode.
 * Version: 1.0
 * Author: Chidera Caleb
 */

if (!defined('ABSPATH')) exit;

// Register Custom Post Type
function cp_register_client_project() {
    register_post_type('client_project', [
        'label' => 'Client Projects',
        'public' => true,
        'supports' => ['title'],
        'has_archive' => true,
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true,
    ]);
}
add_action('init', 'cp_register_client_project');

// Add Meta Boxes
function cp_add_meta_boxes() {
    add_meta_box('cp_project_details', 'Project Details', 'cp_render_meta_box', 'client_project', 'normal', 'default');
}
add_action('add_meta_boxes', 'cp_add_meta_boxes');

// Render Meta Box
function cp_render_meta_box($post) {
    $client_name = get_post_meta($post->ID, '_cp_client_name', true);
    $description = get_post_meta($post->ID, '_cp_description', true);
    $status = get_post_meta($post->ID, '_cp_status', true);
    $deadline = get_post_meta($post->ID, '_cp_deadline', true);
    ?>
<p><label>Client Name:</label><br>
    <input type="text" name="cp_client_name" value="<?php echo esc_attr($client_name); ?>" style="width: 100%;" />
</p>
<p><label>Description:</label><br>
    <textarea name="cp_description" rows="3" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
</p>
<p><label>Status:</label><br>
    <input type="text" name="cp_status" value="<?php echo esc_attr($status); ?>" style="width: 100%;" />
</p>
<p><label>Deadline:</label><br>
    <input type="date" name="cp_deadline" value="<?php echo esc_attr($deadline); ?>" />
</p>
<?php
}

// Save Meta Data
function cp_save_meta_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, '_cp_client_name', sanitize_text_field($_POST['cp_client_name'] ?? ''));
    update_post_meta($post_id, '_cp_description', sanitize_textarea_field($_POST['cp_description'] ?? ''));
    update_post_meta($post_id, '_cp_status', sanitize_text_field($_POST['cp_status'] ?? ''));
    update_post_meta($post_id, '_cp_deadline', sanitize_text_field($_POST['cp_deadline'] ?? ''));
}
add_action('save_post', 'cp_save_meta_data');

// Register Shortcode [client_projects]
function cp_client_projects_shortcode($atts) {
    $projects = new WP_Query(['post_type' => 'client_project', 'posts_per_page' => -1]);

    if (!$projects->have_posts()) return '<p>No projects found.</p>';

    $output = '<ul>';
    while ($projects->have_posts()) {
        $projects->the_post();
        $client = get_post_meta(get_the_ID(), '_cp_client_name', true);
        $status = get_post_meta(get_the_ID(), '_cp_status', true);
        $deadline = get_post_meta(get_the_ID(), '_cp_deadline', true);
        $desc = get_post_meta(get_the_ID(), '_cp_description', true);
        $output .= "<li><strong>" . esc_html(get_the_title()) . "</strong><br>
                    Client: " . esc_html($client) . "<br>
                    Status: " . esc_html($status) . "<br>
                    Deadline: " . esc_html($deadline) . "<br>
                    " . esc_html($desc) . "</li><hr>";
    }
    $output .= '</ul>';
    wp_reset_postdata();
    return $output;
}
add_shortcode('client_projects', 'cp_client_projects_shortcode');