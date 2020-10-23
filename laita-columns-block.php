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

/**
 * Helper: Zjednodušuje volání funkcí v namespace
 *
 * @param string $callable
 * @return string
 */
function ns(string $callable)
{
    return __NAMESPACE__ . '\\' . $callable;
}

/**
 * Registrace custom bloků
 */
add_action('init', ns('register_column_block'));
add_action('init', ns('register_columns_block'));

/**
 * Render pro core bloky a nahrazení render funkce za vlastní. Pokud
 * chceš použít kontext, musíš využít toto místo render_block filteru.
 */
add_filter('register_block_type_args', ns('register_render_with_context'), 10, 2);


/**
 * Registruje laita/columns block
 */
function register_columns_block()
{
    // Zaregistrujeme styl pro frontend
    wp_register_style(
        'laita-columns-frontend',
        plugin_dir_url(__FILE__) . 'assets/columns-frontend.css'
    );

    // Zaregistrujeme styl pro block editor
    wp_register_style(
        'laita-columns-block-style',
        plugin_dir_url(__FILE__) . 'assets/columns-block.css'
    );

    // Zaregistrujeme script do block editoru
    wp_register_script(
        'laita-columns-block-script',
        plugin_dir_url(__FILE__) . 'assets/columns-block.js',
        ['react', 'wp-blocks', 'wp-block-editor', 'wp-i18n']
    );

    // Nastavíme překlady pro blok
    wp_set_script_translations('laita-columns-block-script', 'laita-columns', plugin_dir_path(__FILE__) . 'languages');

    // Zaregistrujeme vlastní block type
    register_block_type('laita/columns', [
        'editor_script'    => 'laita-columns-block-script',
        'editor_style'     => 'laita-columns-block-style',
        'style'            => 'laita-columns-frontend',
        'attributes'       => [
            'columns' => ['type' => 'number'],
            'align'   => ['type' => 'string'],
        ],
        'provides_context' => [
            'laita/columns' => 'columns', // tady kontext poskytuješ pro podřízené bloky
        ],
    ]);
}

function register_column_block()
{
    // Zaregistrujeme styl pro block editor
    wp_register_style(
        'laita-column-block-style',
        plugin_dir_url(__FILE__) . 'assets/column-block.css'
    );

    // Zaregistrujeme script do block editoru
    wp_register_script(
        'laita-column-block-script',
        plugin_dir_url(__FILE__) . 'assets/column-block.js',
        ['react', 'wp-blocks', 'wp-block-editor', 'wp-i18n']
    );

    // Nastavíme překlady pro blok
    wp_set_script_translations('laita-column-block-script', 'laita-columns', plugin_dir_path(__FILE__) . 'languages');

    // Zaregistrujeme vlastní block type
    register_block_type('laita/column', [
        'editor_script'    => 'laita-column-block-script',
        'editor_style'     => 'laita-column-block-style',
        'attributes'       => [
            'column' => ['type' => 'number'],
        ],
        'provides_context' => [
            'laita/column' => 'column', // tady kontext poskytuješ pro podřízené bloky
        ],
    ]);
}

/**
 * Zaregistruje u libovolného bloku kontext, který má odebírat
 * a vlastní render funkce, které můžou kontext využívat.
 *
 * @param $args
 * @param $block_name
 * @return mixed
 */
function register_render_with_context($args, $block_name)
{
    // všechny bloky budou využívat context
    if (!isset($args['uses_context'])) {
        $args['uses_context'] = [];
    }

    $args['uses_context'] = array_merge($args['uses_context'], [
        'laita/columns',
        'laita/column',
    ]);

    // registrujeme vlastní render funkce
    $custom_renders = [
        'laita/columns' => ns('render_columns'),
        'laita/column'  => ns('render_column'),
        'core/image'    => ns('render_image'),
        'core/gallery'  => ns('render_gallery'),
    ];

    if ($custom_renders[$block_name]) {
        $args['render_callback'] = $custom_renders[$block_name];
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
    <div class="<?php echo join(' ', ['laita-column', 'laita-column--' . $attributes['column'], 'laita-column--in-' . $block->context['laita/columns']]) ?>">
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
        $attributes['className'] ?? null,
        'image-in-columns--' . $block->context['laita/columns'], // tady už můžeš kontext používat
        'image--column-' . $block->context['laita/column'],
    ]); ?>">
        <?php echo wp_get_attachment_image($attributes['id'], $attributes['sizeSlug'], false, [
            'class' => $attributes['className'] ?? null,
            'alt'   => $attributes['alt'],
        ]); ?>
    </figure>
    <?php
    return ob_get_clean();
}

/**
 * Vlastní render galerie
 *
 * @param array $attributes
 * @param null $content
 * @param null $block
 * @return mixed|null
 */
function render_gallery($attributes = [], $content = null, $block = null)
{
    return $content;
}
