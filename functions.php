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
	    
    }
	public function hello_elementor_child_scripts_styles() {
	
		wp_enqueue_style('hello-elementor-child-style',get_stylesheet_directory_uri() . '/style.css',['hello-elementor-theme-style'], HELLO_ELEMENTOR_CHILD_VERSION);
	
	}

	public function wp_head_func(){
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
	    $head_content = ob_get_clean();
	    preg_match_all('/<meta[^>]+>/', $head_content, $meta_tags);
	    preg_match_all('/<link[^>]+>/', $head_content, $link_tags);
	    $head_content_sin_meta_link = preg_replace('/<(meta|link)[^>]+>/', '', $head_content);
	    echo '<!-- ola.marketing -->';
	    if (!empty($meta_tags[0])) {
	        echo implode("\n", $meta_tags[0]) . "\n";
	    }
	    if (!empty($link_tags[0])) {
	        echo implode("\n", $link_tags[0]) . "\n";
	    }
	    echo $head_content_sin_meta_link;
	}

}
$GLOBALS['HelloElementorOptimizedChild'] = HelloElementorOptimizedChild::get_instance();

