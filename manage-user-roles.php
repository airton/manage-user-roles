<?php
/**
 * Plugin Name:       Manage User Roles
 * Plugin URI:        https://airtonvancin.com/plugin/manage-user-roles
 * Description:       Restricts users to only see their own posts in the WordPress admin, with advanced role-based rules.
 * Version:           2.1.0
 * Author:            Airton Vancin Junior
 * Author URI:        https://airtonvancin.com
 * Text Domain:       manage-user-roles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define valores padrão ao ativar o plugin.
 */
function mur_activate()
{
    // Define um array vazio para as configurações, a lógica lidará com os padrões.
    add_option('mur_role_settings', array());
}
register_activation_hook(__FILE__, 'mur_activate');

/**
 * Carrega o textdomain para traduções.
 */
function mur_load_textdomain()
{
    load_plugin_textdomain('manage-user-roles', false, plugin_basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mur_load_textdomain');

/**
 * Helper: Determina a regra de visualização para um usuário.
 *
 * @param WP_User $user Objeto do usuário.
 * @return string 'own_content' ou 'all_content'.
 */
function mur_get_view_rule_for_user($user)
{
    if (empty($user->roles) || in_array('administrator', $user->roles)) {
        return 'all_content';
    }

    $settings = get_option('mur_role_settings', array());
    $user_role = $user->roles[0]; // Pega a role primária do usuário.

    // Se não houver regra para esta role, aplica a padrão (ver apenas o próprio conteúdo).
    return isset($settings[$user_role]['view_rule']) ? $settings[$user_role]['view_rule'] : 'own_content';
}

/**
 * Filtra a consulta de posts com base nas regras de role.
 */
function mur_limit_posts_per_user($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $current_user = wp_get_current_user();
    $rule = mur_get_view_rule_for_user($current_user);

    // Filter validation for Media Library (Attachments)
    if ($query->get('post_type') === 'attachment') {
        $media_restriction = get_option('mur_media_restriction_enabled', '1');
        if (!$media_restriction) {
            return; // Media restrictions disabled globally
        }
    }

    if ('own_content' === $rule) {
        $query->set('author', $current_user->ID);
    }
}
add_action('pre_get_posts', 'mur_limit_posts_per_user');

/**
 * Filtra a biblioteca de mídia (Grid View / Modal) via AJAX.
 */
function mur_filter_media_library_ajax($query)
{
    $media_restriction = get_option('mur_media_restriction_enabled', '1');
    if (!$media_restriction) {
        return $query; // Media restrictions disabled globally
    }

    $current_user = wp_get_current_user();
    $rule = mur_get_view_rule_for_user($current_user);

    if ('own_content' === $rule) {
        $query['author'] = $current_user->ID;
    }

    return $query;
}
add_filter('ajax_query_attachments_args', 'mur_filter_media_library_ajax');
add_action('pre_get_posts', 'mur_limit_posts_per_user');

/**
 * Remove o botão de editar da admin bar se o usuário não for o autor.
 */
function mur_remove_items_admin_bar()
{
    $current_user = wp_get_current_user();
    if (in_array('administrator', $current_user->roles)) {
        return;
    }

    $rule = mur_get_view_rule_for_user($current_user);

    if ('own_content' === $rule && is_singular()) {
        global $wp_admin_bar;
        $post_author_id = get_the_author_meta('ID');
        if ($current_user->ID != $post_author_id) {
            $wp_admin_bar->remove_menu('edit');
        }
    }
}
add_action('wp_before_admin_bar_render', 'mur_remove_items_admin_bar');

/**
 * Adiciona a página de configurações.
 */
function mur_add_settings_page()
{
    add_options_page(
        __('Manage User Roles Settings', 'manage-user-roles'),
        __('Manage User Roles', 'manage-user-roles'),
        'manage_options',
        'manage-user-roles',
        'mur_render_settings_page'
    );
}
add_action('admin_menu', 'mur_add_settings_page');

/**
 * Renderiza a página de configurações.
 */
function mur_render_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('mur_settings_group');
            do_settings_sections('manage-user-roles');
            submit_button(__('Save Changes', 'manage-user-roles'));
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registra as seções e campos da página de configurações.
 */
function mur_register_settings()
{
    register_setting(
        'mur_settings_group',
        'mur_role_settings',
        'mur_sanitize_role_settings' // Adiciona o callback de sanitização.
    );

    register_setting(
        'mur_settings_group',
        'mur_media_restriction_enabled',
        'absint'
    );

    add_settings_section(
        'mur_general_section',
        __('General Settings', 'manage-user-roles'),
        'mur_general_section_callback',
        'manage-user-roles'
    );

    add_settings_field(
        'mur_media_restriction_field',
        __('Restrict Media Library Access', 'manage-user-roles'),
        'mur_media_restriction_callback',
        'manage-user-roles',
        'mur_general_section'
    );

    add_settings_section(
        'mur_roles_section',
        __('Role Viewing Rules', 'manage-user-roles'),
        'mur_roles_section_callback',
        'manage-user-roles'
    );

    $roles = get_editable_roles();
    unset($roles['administrator']); // Remove a role de administrador da lista.

    foreach ($roles as $role_slug => $role_details) {
        add_settings_field(
            'mur_role_field_' . $role_slug,
            $role_details['name'],
            'mur_role_field_callback',
            'manage-user-roles',
            'mur_roles_section',
            ['slug' => $role_slug, 'name' => $role_details['name']]
        );
    }
}
add_action('admin_init', 'mur_register_settings');

/**
 * Callback da seção de roles.
 */
function mur_roles_section_callback()
{
    echo '<p>' . esc_html__('Define content viewing permissions for each user role. Administrators always see all content.', 'manage-user-roles') . '</p>';
}

/**
 * Callback da seção geral.
 */
function mur_general_section_callback()
{
    echo '<p>' . esc_html__('Global settings for content visibility.', 'manage-user-roles') . '</p>';
}

/**
 * Callback para o campo de restrição de mídia.
 */
function mur_media_restriction_callback()
{
    $enabled = get_option('mur_media_restriction_enabled', '1');
    echo '<label><input type="checkbox" name="mur_media_restriction_enabled" value="1" ' . checked(1, $enabled, false) . ' /> ' . esc_html__('Apply "View only own content" restrictions to the Media Library (Grid & List).', 'manage-user-roles') . '</label>';
    echo '<p class="description">' . esc_html__('If disabled, users can see all images in the Media Library regardless of their post viewing rules.', 'manage-user-roles') . '</p>';
}

/**
 * Callback para renderizar os campos de cada role.
 */
function mur_role_field_callback($args)
{
    $settings = get_option('mur_role_settings', array());
    $role_slug = $args['slug'];
    $current_rule = isset($settings[$role_slug]['view_rule']) ? $settings[$role_slug]['view_rule'] : 'own_content';

    $rules = [
        'own_content' => __('View only own content', 'manage-user-roles'),
        'all_content' => __('View all content (no restrictions)', 'manage-user-roles'),
    ];

    printf(
        '<select name="%s">',
        esc_attr("mur_role_settings[{$role_slug}][view_rule]")
    );

    foreach ($rules as $rule_slug => $rule_name) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr($rule_slug),
            selected($current_rule, $rule_slug, false),
            esc_html($rule_name)
        );
    }
    echo "</select>";
}

/**
 * Sanitize e valida as opções do plugin antes de salvar.
 *
 * @param array $input As opções enviadas pelo formulário.
 * @return array As opções sanitizadas.
 */
function mur_sanitize_role_settings($input)
{
    $sanitized_input = array();
    $allowed_rules = array('own_content', 'all_content');
    $roles = get_editable_roles();
    unset($roles['administrator']);

    foreach ($roles as $role_slug => $role_details) {
        if (isset($input[$role_slug]['view_rule']) && in_array($input[$role_slug]['view_rule'], $allowed_rules, true)) {
            $sanitized_input[$role_slug]['view_rule'] = $input[$role_slug]['view_rule'];
        } else {
            $sanitized_input[$role_slug]['view_rule'] = 'own_content';
        }
    }

    return $sanitized_input;
}

/**
 * Adiciona links de ação na lista de plugins.
 */
function mur_plugin_action_links($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=manage-user-roles') . '">' . __('Settings', 'manage-user-roles') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mur_plugin_action_links');

function mur_plugin_row_meta($plugin_meta, $plugin_file)
{
    if ($plugin_file !== 'manage-user-roles/manage-user-roles.php') {
        return $plugin_meta;
    }

    $plugin_meta[] = '<a href="https://www.buymeacoffee.com/airton" target="_blank">' . __('☕️ Buy me a coffee', 'manage-user-roles') . '</a>';
    $plugin_meta[] = '<a href="https://airtonvancin.com/plugin/manage-user-roles" target="_blank">' . __('Visite plugin page', 'manage-user-roles') . '</a>';

    return $plugin_meta;
}

add_filter('plugin_row_meta_' . plugin_basename(__FILE__), 'mur_plugin_row_meta', 10, 2);