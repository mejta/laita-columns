(function () {
  const { createElement: e } = React;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks } = wp.blockEditor;
  const { __ } = wp.i18n;
  
  const EditColumns = (props) => {
    const { className, attributes } = props;
    
    const template = (new Array(attributes.columns))
      .fill()
      .map(
        (column, index) => ['laita/column', { column: index + 1 }]
      );
    
    // Documentation: https://reactjs.org/docs/react-without-jsx.html
    return e('div', { className }, [
      e(InnerBlocks, {
        allowedBlocks: ['laita/column'],
        orientation: 'horizontal',
        template,
        templateLock: 'all'
      }),
    ]);
  };
  
  // Documentation: https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
  registerBlockType('laita/columns', {
    title: __('Custom columns', 'laita-columns'),
    description: __('Custom columns brought to you by Adam Laita', 'laita-columns'),
    category: 'layout',
    icon: 'format-aside',
    edit: EditColumns,
    save: () => e(InnerBlocks.Content),
    supports: {
      anchor: true,
      align: ['full'],
      alignWide: true,
      html: false,
      inserter: true,
      reusable: true,
    },
    variations: [
      {
        name: 'two-columns',
        title: __('Two columns', 'laita-columns'),
        description: __('Two custom columns', 'laita-columns'),
        icon: 'format-aside',
        isDefault: true,
        scope: 'inserter',
        attributes: {
          columns: 2,
        },
        innerBlocks: [
          ['laita/column', { column: 1 }],
          ['laita/column', { column: 2 }],
        ],
      },
      {
        name: 'three-columns',
        title: __('Three columns', 'laita-columns'),
        description: __('Three custom columns', 'laita-columns'),
        icon: 'format-aside',
        isDefault: false,
        scope: 'inserter',
        attributes: {
          columns: 3,
        },
        innerBlocks: [
          ['laita/column', { column: 1 }],
          ['laita/column', { column: 2 }],
          ['laita/column', { column: 3 }],
        ],
      },
    ],
    styles: [
      {
        name: 'laita-columns-50-50',
        label: __('50/50', 'laita-columns'),
        isDefault: true,
      },
      {
        name: 'laita-columns-30-70',
        label: __('30/70', 'laita-columns'),
      },
      {
        name: 'laita-columns-70-30',
        label: __('70/30', 'laita-columns'),
      },
    ]
  });
})();
