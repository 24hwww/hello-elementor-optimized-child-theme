<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorOptimizedChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '1.0.4' );

/**
 * Load optimized child theme scripts & styles.
 *
 * @return void
 */

class HelloElementorOptimizedChild{
	private static $instance = null;

	public static function get_instance(){
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    function __construct() {

		add_action('admin_init', array($this,'check_update_theme_func'));

		add_action('wp_enqueue_scripts', array($this,'hello_elementor_child_scripts_styles'), 20 );
		add_action('wp_head', array($this,'wp_head_func'));
		add_action('wp_head', array($this,'capturar_contenido_head'), 0);
		add_action('wp_head', array($this,'agrupar_meta_y_link'), 999);
		add_filter('the_generator', '__return_null' );

		add_filter('style_loader_src', array($this,'remove_css_js_version'), 9999 );
		add_filter('script_loader_src', array($this,'remove_css_js_version'), 9999 );		
		add_filter('style_loader_tag', array($this,'delay_rel_preload_func'), 10, 4 );

		if ( did_action( 'elementor/loaded' ) ) {
			add_action('wp_body_open', array($this,'inicio_contenido'),10);
			add_action('wp_footer', array($this,'final_resultado_contenido'),10);		
		}
    }

	private function curl_get_contents($url,$agent=''){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$data = curl_exec($ch);
		$codigo_estado = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $codigo_estado == '200' ? $data : false;
	}

	public function check_update_theme_func(){
		$get_theme = wp_get_theme();
		$author = !empty($get_theme->get( 'Author' )) ? $get_theme->get( 'Author' ) : '';
		$template = !empty($get_theme->get( 'ThemeURI' )) ? basename($get_theme->get( 'ThemeURI' )) : '';
		$version_actual = !empty($get_theme->get( 'Version' )) ? basename($get_theme->get( 'Version' )) : '';

		$repositorio = "{$author}/{$template}";
		$url_release = "https://api.github.com/repos/{$repositorio}/releases/latest";
		$datos = $this->curl_get_contents($url_release,$template);

		if ($datos === FALSE) { return false; }

		$release = json_decode($datos, true);
		$version_disponible = isset($release['tag_name']) ? $release['tag_name'] : false;

		if ($version_disponible === FALSE) { return false; }

		if (version_compare($version_actual, $version_disponible, '<')) {
			add_action('admin_notices', array($this,'admin_notices_check_update_theme_func'));
		}
	}

	public function admin_notices_check_update_theme_func(){
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong><?php _e('New theme update available!', 'default'); ?></strong> 
			<?php printf(__('A new version of your theme is available. <a href="%s" target="_blank">Click here to get it</a>.', 'default'), 'https://github.com/usuario/repositorio/releases'); ?></p>
		</div>
		<?php
	}

	public function hello_elementor_child_scripts_styles() {

		wp_dequeue_style( 'classic-theme-styles' );
	
		wp_enqueue_style('hello-elementor-child-style',get_stylesheet_directory_uri() . '/style.css',['hello-elementor-theme-style'], HELLO_ELEMENTOR_CHILD_VERSION);
	
		if(!is_admin()): 
			$ver = '3.7.1'; // Update this to change the jQuery version.
			wp_dequeue_script( 'jquery' );
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/' . $ver . '/jquery.min.js', false, $ver, true );
			wp_enqueue_script( 'jquery' );
			/* */
			wp_dequeue_style('global-styles');
			wp_dequeue_style('wp-block-library');
			wp_dequeue_style('wp-block-library-theme');
			wp_dequeue_style('wc-blocks-style'); 
		   	wp_dequeue_script('smartmenus');
		endif;

	}

	public function wp_head_func(){
		remove_action('wp_head', 'wp_generator');
		$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
		$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
		$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );    
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
		<link rel="profile" href="https://gmpg.org/xfn/11">    
		<?php
	}

	public function capturar_contenido_head(){
		ob_start();
	}

	public function agrupar_meta_y_link(){
		$html = '';
	    $head_content = ob_get_clean();
	    preg_match_all('/<meta[^>]+>/', $head_content, $meta_tags);
	    preg_match_all('/<link[^>]+>/', $head_content, $link_tags);
	    $head_content_sin_meta_link = preg_replace('/<(meta|link)[^>]+>/', '', $head_content);
	    $html .= '<!-- ola.marketing -->'. "\n";
	    if (!empty($meta_tags[0])) {
	        $html .= implode("\n", $meta_tags[0]) . "\n";
	    }
	    if (!empty($link_tags[0])) {
	        $html .= implode("\n", $link_tags[0]) . "\n";
	    }
	    echo $html . $head_content_sin_meta_link;
	}

	public function remove_css_js_version($src){
		if( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    	return $src;
	}

	public function delay_rel_preload_func($tag, $handle, $src, $media){
		if (is_admin()) return $tag;
		if (str_contains($handle, 'widget-')) { return $tag; }
	
        $nonce = wp_create_nonce();
        $nonce = " nonce='{$nonce}'";
        
        ob_start();
        ?>
        <link rel="preload" id="<?php echo $handle; ?>-css" href="<?php echo $src; ?>" as='style' media="<?php echo $media; ?>" <?php echo $nonce; ?> />
        <script <?php echo $nonce; ?>>
        let css = document.getElementById('<?php echo $handle; ?>-css');
        css.addEventListener("load", function( e ){ 
        e.currentTarget.rel='stylesheet';
        },{once:true});
        </script>
        <noscript><?php echo trim($tag); ?></noscript>
        <?php
        $ga = ob_get_contents();
		ob_end_clean();
		$_tag = preg_replace('/[\x00-\x1F\xFF]/','',$ga);
		
        return $tag;
	}

	public function inicio_contenido(){
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) { return false;}
		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {return false;}
		ob_start();
	}

	public function final_resultado_contenido(){
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) { return false;}
		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {return false;}
		$code = ob_get_clean();
		$cleaned_code = trim(preg_replace('/\s+/', ' ', $code));
		$formatted_code = preg_replace('/>\s*</', ">\n<", $cleaned_code);
		echo '<!-- ----- Optimized ----- -->'."\n";
		echo $formatted_code;
	}	

}
$GLOBALS['HelloElementorOptimizedChild'] = HelloElementorOptimizedChild::get_instance();
