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

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '1.0.0' );

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

		add_action('wp_enqueue_scripts', array($this,'hello_elementor_child_scripts_styles'), 20 );
		add_action('wp_head', array($this,'wp_head_func'));
		add_action('wp_head', array($this,'capturar_contenido_head'), 0);
		add_action('wp_head', array($this,'agrupar_meta_y_link'), 999);
		add_filter( 'the_generator', '__return_null' );

		/*add_filter('the_content', array($this,'convertir_url_absoluta_a_relativa'));
		add_filter('script_loader_src', array($this,'convertir_url_absoluta_a_relativa'));
		add_filter('style_loader_src', array($this,'convertir_url_absoluta_a_relativa'));
		add_filter('wp_get_attachment_url', array($this,'convertir_url_absoluta_a_relativa'));
		add_filter('post_thumbnail_html', array($this,'convertir_url_absoluta_a_relativa'));*/

		//add_filter( 'style_loader_tag', array($this,'delay_rel_preload_func'), 10, 4 );

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

	public function convertir_url_absoluta_a_relativa($url){
		if (is_admin()) return $url;
		// Verifica si la URL es absoluta
		if (strpos($url, home_url()) === 0) {
			// Si es una URL dentro de tu dominio, la convertimos a relativa
			return preg_replace('#^' . preg_quote(home_url(), '#') . '#', '', $url);
		}
		return $url; // Si no, la dejamos igual
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

}
$GLOBALS['HelloElementorOptimizedChild'] = HelloElementorOptimizedChild::get_instance();

