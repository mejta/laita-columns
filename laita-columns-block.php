<?php
/*
Plugin Name: Laita Columns block
Version:     1.0.0
Author:      Daniel Mejta
Author URI:  https://www.mejta.net
Text Domain: laita-columns
Domain Path: /languages
*/

namespace Laita;

// Načtení překladů
add_action('init', __NAMESPACE__ . '\load_translations');

// Registrace custom bloků
add_action('init', __NAMESPACE__ . '\register_blocks');

// Render pro core bloky a nahrazení render funkce za vlastní. Pokud chceš použít kontext, musíš využít toto místo render_block.
add_filter('register_block_type_args', __NAMESPACE__ . '\register_image_render_with_context', 10, 2);

/**
 * Registruje vlastní bloky
 */
function register_blocks()
{
    $blocks = [
        'laita/column'  => [
            'script'   => [// Javascript do block editoru
                'handle' => 'laita-column-block-script',
                'deps'   => ['react', 'wp-blocks', 'wp-block-editor', 'wp-i18n'],
                'path'   => 'assets/column-block.js',
            ],
            'style'    => [// Styly pro block editor
                'handle' => 'laita-column-block-style',
                'path'   => 'assets/column-block.css',
            ],
            'fe_style' => [ // Styly pro frontend
                'handle' => null,
                'path'   => null,
            ],
            'args'     => [ // Ostatní argumenty do register_block_type() funkce
                'attributes'      => [
                    'columns' => ['type' => 'string'],
                ],
                'uses_context' => ['laita/columns'],
                'render_callback' => __NAMESPACE__ . '\render_column', // funkce pro render custom bloku
            ],
        ],
        'laita/columns' => [
            'script'   => [
                'handle' => 'laita-columns-block-script',
                'deps'   => ['react', 'wp-blocks', 'wp-block-editor', 'wp-i18n'],
                'path'   => 'assets/columns-block.js',
            ],
            'style'    => [
                'handle' => 'laita-columns-block-style',
                'path'   => 'assets/columns-block.css',
            ],
            'fe_style' => [
                'handle' => 'laita-columns-style',
                'path'   => 'assets/columns-frontend.css',
            ],
            'args'     => [
                'attributes'      => [
                    'columns'          => ['type' => 'number'],
                    'align'            => ['type' => 'string'],
                ],
                'render_callback' => __NAMESPACE__ . '\render_columns',
                'provides_context' => [
                    'laita/columns' => 'columns', // tady kontext poskytuješ pro podřízené bloky
                ],
            ],
        ],
    ];

    foreach ($blocks as $name => $block) {
        // Zaregistrujeme styl pro frontend
        wp_register_style($block['fe_style']['handle'], plugin_dir_url(__FILE__) . $block['fe_style']['path']);

        // Zaregistrujeme styl pro block editor
        wp_register_style($block['style']['handle'], plugin_dir_url(__FILE__) . $block['style']['path']);

        // Zaregistrujeme script do block editoru
        wp_register_script($block['script']['handle'], plugin_dir_url(__FILE__) . $block['script']['path'], $block['script']['deps']);

        // Nastavíme překlady pro blok
        wp_set_script_translations($block['script']['handle'], 'laita-columns', plugin_dir_path(__FILE__) . 'languages');

        // Zaregistrujeme vlastní block tyle
        register_block_type(
            $name,
            array_merge(
                [
                    'editor_script' => $block['script']['handle'],
                    'editor_style'  => $block['style']['handle'],
                    'style'         => $block['fe_style']['handle'],
                ],
                $block['args']
            )
        );
    }
}

/**
 * Načítá překlady pro plugin
 */
function load_translations()
{
    load_plugin_textdomain('laita-columns', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Zaregistruje u libovolného bloku kontext, který má odebírat. Můžeš to pak použít ve vlastním renderu
 *
 * @param $args
 * @param $block_name
 * @return mixed
 */
function register_image_render_with_context($args, $block_name)
{
    if ($block_name === 'core/image') {
        $args['render_callback'] = __NAMESPACE__ . '\render_image';
        $args['uses_context'] = array_merge($args['uses_context'] ?? [], [
            'laita/columns',
        ]);
    }

    return $args;
}

/**
 * Vypisuje wrapper pro sloupce
 *
 * @param array $attributes
 * @param null $inner_blocks
 * @return false|string
 */
function render_columns($attributes = [], $inner_blocks = null)
{
    ob_start();
    ?>
    <div class="<?php echo join(' ', ['laita-columns', 'laita-columns--' . $attributes['columns'], $attributes['className'] ?? null]); ?>">
        <?php echo $inner_blocks; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Vypisuje jednotlivý sloupec
 *
 * @param array $attributes
 * @param null $inner_blocks
 * @param null $block
 * @return false|string
 */
function render_column($attributes = [], $inner_blocks = null, $block = null)
{
    ob_start();
    ?>
    <div class="<?php echo join(' ', ['laita-column', 'laita-column--in-' . $block->context['laita/columns']])?>">
        <?php echo $inner_blocks; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Vlastní render obrázku
 *
 * @param string $block_content
 * @param array $block
 * @return string
 */
function render_image($attributes = [], $content = null, $block = null)
{
    ob_start();
    ?>
    <figure class="<?php echo join(' ', [
        $attributes['className'],
        'image-in-columns--' . $block->context['laita/columns'], // tady už můžeš kontext používat
    ]); ?>">
        <?php echo wp_get_attachment_image($attributes['id'], $attributes['sizeSlug'], false, [
            'class' => $attributes['className'],
            'alt' => $attributes['alt'],
        ]); ?>
    </figure>
    <?php
    return ob_get_clean();
}

/**
 * Jednoduchá funkce na dump proměnné
 *
 * @param mixed ...$vars
 */
function dump()
{
$backtrace = debug_backtrace();
$caller = $backtrace[0];
?>
<style>
.simple-dumper {
border: 1px solid red;
background: rgba(0,0,0,0.5);
color: white;
font-size: 1rem;
position: relative;
}
.simple-dumper h1 {
font-size: 1rem;
padding: 0.25rem 0.5rem;
margin: 0;
position: absolute;
top: -1rem;
left: 1rem;
background: red;
}
.simple-dumper pre {
margin: 0;
padding: 1rem 1rem 0.5rem 1rem;
}
</style>
<div class="simple-dumper">
<h1><?php echo $caller['file'] . ':' . $caller['line']; ?></h1>
<pre>
<?php echo var_export(func_get_args()); ?>
</pre>
</div><?php
}
