(function () {
  const { createElement: e } = React;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks } = wp.blockEditor;
  const { __ } = wp.i18n;
  
  const EditColumn = (props) => {
    const { className } = props;
    
    // Documentation: https://reactjs.org/docs/react-without-jsx.html
    return e('div', { className }, [
      e(InnerBlocks, { templateLock: false }),
    ]);
  };
  
  // Documentation: https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
  registerBlockType('laita/column', {
    title: __('Custom column', 'laita-columns'),
    description: __('Custom column brought to you by Adam Laita', 'laita-columns'),
    category: 'layout',
    icon: 'format-aside',
    edit: EditColumn,
    save: () => e(InnerBlocks.Content),
    supports: {
      anchor: false,
      align: false,
      alignWide: false,
      html: false,
      inserter: false,
      reusable: false,
    },
  });
})();
