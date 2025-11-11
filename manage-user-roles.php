<?php
/**
 * Plugin Name:       Manage User Roles
 * Plugin URI:        https://github.com/airton/manage-user-roles
 * Description:       Restricts users to only see their own posts in the WordPress admin.
 * Version:           1.2.0
 * Author:            Airton Vancin
 * Author URI:        https://airtonvancin.com
 * Text Domain:       manage-user-roles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/airton/manage-user-roles
 */

// Previne acesso direto
if( ! defined( 'ABSPATH' ) ){
	exit;
}

/**
 * Define o valor padrão da opção ao ativar o plugin.
 */
function mur_activate() {
    add_option( 'mur_plugin_enabled', '1' );
}
register_activation_hook( __FILE__, 'mur_activate' );

/**
 * Carrega arquivos de traduções.
 */
function mur_load_textdomain() {
	load_plugin_textdomain( 'manage-user-roles', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'mur_load_textdomain' );

/**
 * Limita os posts para cada usuário.
 */
function mur_limit_posts_per_user( $query ) {
    if ( get_option( 'mur_plugin_enabled', '1' ) !== '1' ) {
        return;
    }

	$current_user = wp_get_current_user();
	$author_role = $current_user->roles[0];
	$author_id = $current_user->ID;

    if( is_admin() && $query->is_main_query() && $author_role != 'administrator' ) {
        $query->set( 'author__in', array($author_id) );
    }
}
add_action( 'pre_get_posts', 'mur_limit_posts_per_user' );

/**
 * Remove o botão de editar post da barra de administração.
 */
function mur_remove_items_admin_bar() {
    if ( get_option( 'mur_plugin_enabled', '1' ) !== '1' ) {
        return;
    }

	global $wp_admin_bar;

	if( is_singular() ){
		$author_id_post = get_the_author_meta('ID');
		$current_user = wp_get_current_user();
		$author_role = $current_user->roles[0];
		$author_id = $current_user->ID;

		if( $author_id_post != $author_id && $author_role != 'administrator' ){
			$wp_admin_bar->remove_menu('edit');
		}
	}
}
add_action( 'wp_before_admin_bar_render', 'mur_remove_items_admin_bar' );

/**
 * Adiciona a página de configurações do plugin.
 */
function mur_add_settings_page() {
    add_options_page(
        'Manage User Roles Settings',
        'Manage User Roles',
        'manage_options',
        'manage-user-roles',
        'mur_render_settings_page'
    );
}
add_action( 'admin_menu', 'mur_add_settings_page' );

/**
 * Renderiza o HTML da página de configurações.
 */
function mur_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'mur_settings_group' );
            do_settings_sections( 'manage-user-roles' );
            submit_button( 'Salvar Alterações' );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registra as configurações do plugin.
 */
function mur_register_settings() {
    register_setting( 'mur_settings_group', 'mur_plugin_enabled' );

    add_settings_section(
        'mur_general_section',
        'Configurações Gerais',
        'mur_general_section_callback',
        'manage-user-roles'
    );

    add_settings_field(
        'mur_plugin_enabled_field',
        'Ativar Restrições',
        'mur_plugin_enabled_field_callback',
        'manage-user-roles',
        'mur_general_section'
    );
}
add_action( 'admin_init', 'mur_register_settings' );

/**
 * Callback da seção de configurações.
 */
function mur_general_section_callback() {
    echo '<p>Controle as configurações principais do plugin aqui.</p>';
}

/**
 * Callback do campo de ativação.
 */
function mur_plugin_enabled_field_callback() {
    $option = get_option( 'mur_plugin_enabled', '1' );
    ?>
    <label for="mur_plugin_enabled">
        <input type="checkbox" id="mur_plugin_enabled" name="mur_plugin_enabled" value="1" <?php checked( '1', $option ); ?> />
        Marque esta caixa para ativar a restrição de posts para usuários não-administradores.
    </label>
    <?php
}
