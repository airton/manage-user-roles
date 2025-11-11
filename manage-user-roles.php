<?php
/**
 * Plugin Name:       Manage User Roles
 * Plugin URI:        https://github.com/airton/manage-user-roles
 * Description:       Restricts users to only see their own posts in the WordPress admin, with advanced role-based rules.
 * Version:           2.0.1
 * Author:            @airton
 * Author URI:        https://airtonvancin.com
 * Text Domain:       manage-user-roles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/airton/manage-user-roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define valores padrão ao ativar o plugin.
 */
function mur_activate() {
    // Define um array vazio para as configurações, a lógica lidará com os padrões.
    add_option( 'mur_role_settings', array() );
}
register_activation_hook( __FILE__, 'mur_activate' );

/**
 * Carrega o textdomain para traduções.
 */
function mur_load_textdomain() {
    load_plugin_textdomain( 'manage-user-roles', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'mur_load_textdomain' );

/**
 * Filtra a consulta de posts com base nas regras de role.
 */
function mur_limit_posts_per_user( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $current_user = wp_get_current_user();
    if ( in_array( 'administrator', $current_user->roles ) ) {
        return; // Administradores não têm restrições.
    }

    $settings = get_option( 'mur_role_settings', array() );
    $user_role = $current_user->roles[0]; // Pega a role primária do usuário.

    // Se não houver regra para esta role, aplica a padrão (ver apenas o próprio conteúdo).
    $rule = isset( $settings[ $user_role ]['view_rule'] ) ? $settings[ $user_role ]['view_rule'] : 'own_content';
    
    if ( 'own_content' === $rule ) {
        $query->set( 'author', $current_user->ID );
    }
    // Adicione aqui futuras lógicas para regras mais complexas (ex: ver posts de outras roles).
}
add_action( 'pre_get_posts', 'mur_limit_posts_per_user' );

/**
 * Remove o botão de editar da admin bar se o usuário não for o autor.
 */
function mur_remove_items_admin_bar() {
    $current_user = wp_get_current_user();
    if ( in_array( 'administrator', $current_user->roles ) ) {
        return;
    }

    $settings = get_option( 'mur_role_settings', array() );
    $user_role = $current_user->roles[0];
    $rule = isset( $settings[ $user_role ]['view_rule'] ) ? $settings[ $user_role ]['view_rule'] : 'own_content';

    if ( 'own_content' === $rule && is_singular() ) {
        global $wp_admin_bar;
        $post_author_id = get_the_author_meta( 'ID' );
        if ( $current_user->ID != $post_author_id ) {
            $wp_admin_bar->remove_menu( 'edit' );
        }
    }
}
add_action( 'wp_before_admin_bar_render', 'mur_remove_items_admin_bar' );

/**
 * Adiciona a página de configurações.
 */
function mur_add_settings_page() {
    add_options_page(
        __( 'Manage User Roles Settings', 'manage-user-roles' ),
        __( 'Manage User Roles', 'manage-user-roles' ),
        'manage_options',
        'manage-user-roles',
        'mur_render_settings_page'
    );
}
add_action( 'admin_menu', 'mur_add_settings_page' );

/**
 * Renderiza a página de configurações.
 */
function mur_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'mur_settings_group' );
            do_settings_sections( 'manage-user-roles' );
            submit_button( __( 'Save Changes', 'manage-user-roles' ) );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registra as seções e campos da página de configurações.
 */
function mur_register_settings() {
    register_setting(
        'mur_settings_group',
        'mur_role_settings',
        'mur_sanitize_role_settings' // Adiciona o callback de sanitização.
    );

    add_settings_section(
        'mur_roles_section',
        __( 'Role Viewing Rules', 'manage-user-roles' ),
        'mur_roles_section_callback',
        'manage-user-roles'
    );

    $roles = get_editable_roles();
    unset( $roles['administrator'] ); // Remove a role de administrador da lista.

    foreach ( $roles as $role_slug => $role_details ) {
        add_settings_field(
            'mur_role_field_' . $role_slug,
            $role_details['name'],
            'mur_role_field_callback',
            'manage-user-roles',
            'mur_roles_section',
            [ 'slug' => $role_slug, 'name' => $role_details['name'] ]
        );
    }
}
add_action( 'admin_init', 'mur_register_settings' );

/**
 * Callback da seção de roles.
 */
function mur_roles_section_callback() {
    echo '<p>' . esc_html__( 'Define content viewing permissions for each user role. Administrators always see all content.', 'manage-user-roles' ) . '</p>';
}

/**
 * Callback para renderizar os campos de cada role.
 */
function mur_role_field_callback( $args ) {
    $settings = get_option( 'mur_role_settings', array() );
    $role_slug = $args['slug'];
    $current_rule = isset( $settings[ $role_slug ]['view_rule'] ) ? $settings[ $role_slug ]['view_rule'] : 'own_content';
    
    $rules = [
        'own_content' => __( 'View only own content', 'manage-user-roles' ),
        'all_content' => __( 'View all content (no restrictions)', 'manage-user-roles' ),
    ];

    printf(
        '<select name="%s">',
        esc_attr( "mur_role_settings[{$role_slug}][view_rule]" )
    );

    foreach ( $rules as $rule_slug => $rule_name ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $rule_slug ),
            selected( $current_rule, $rule_slug, false ),
            esc_html( $rule_name )
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
function mur_sanitize_role_settings( $input ) {
    $sanitized_input = array();
    $allowed_rules = array( 'own_content', 'all_content' );
    $roles = get_editable_roles();
    unset( $roles['administrator'] );

    foreach ( $roles as $role_slug => $role_details ) {
        if ( isset( $input[ $role_slug ]['view_rule'] ) && in_array( $input[ $role_slug ]['view_rule'], $allowed_rules, true ) ) {
            $sanitized_input[ $role_slug ]['view_rule'] = $input[ $role_slug ]['view_rule'];
        } else {
            $sanitized_input[ $role_slug ]['view_rule'] = 'own_content';
        }
    }

    return $sanitized_input;
}
