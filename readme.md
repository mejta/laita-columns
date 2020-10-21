# Laita Columns Block

## How to add another variantion or style

1. Open file `assets/columns-blocks.js` and edit configuration of `registerBlockType`.

## How to translate

1. Create POT file with `wp i18n make-pot ./ languages/laita-colums.pot`. The filename must match the text-domain.
2. Create translations with Poedit.
3. Generate JSON files for javascript translations `wp i18n make-json languages --no-purge`.
4. PROFIT