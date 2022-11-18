[![Build Status](https://github.com/MetaModels/attribute_tablemulti/actions/workflows/diagnostics.yml/badge.svg)](https://github.com/MetaModels/attribute_tablemulti/actions)
[![Latest Version tagged](http://img.shields.io/github/tag/MetaModels/attribute_tablemulti.svg)](https://github.com/MetaModels/attribute_tablemulti/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/MetaModels/attribute_tablemulti.svg)](https://packagist.org/packages/MetaModels/attribute_tablemulti)
[![Installations via composer per month](http://img.shields.io/packagist/dm/MetaModels/attribute_tablemulti.svg)](https://packagist.org/packages/MetaModels/attribute_tablemulti)

# The table multi attribute

The table multi attribute for MetaModels.


## Original idea by Byteworks:

- [Ronny Binder](mailto:rb@bytworks.ch)
- [Michael Bischof](mailto:mb@byteworks.ch)


## Configure the table multi attribute

With this attribute you are able to create complex table structures with the [MultiColumnWizard]( https://github.com/menatwork/contao-multicolumnwizard-bundle).
Create the configuration in e.g. the app/Resources/contao/config/dcaconfig.php or somewhere else where the config is loaded and write something like this:


The `mm_test` is the name of the table and the `multi_test` is the name of the field.

```php
$GLOBALS['TL_CONFIG']['metamodelsattribute_multi']['mm_test']['multi_test'] = array(
    'tl_class'     => 'clr',
    'minCount'     => 0,
    'columnFields' => array(
        'col_title' => array(
            'label'     => 'Title',
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'style'=>'width:130px'
            )
        ),
        'col_highlight' => array(
            'label'     => 'Hervorheben',
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'style' => 'width:40px'
            )
        ),
        'col_url' => array(
            'label'     => 'URL',
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'style'    =>'width:130px', 
                'mandatory'=>false, 
                'rgxp'     =>'url'
            )
        ),
    ),
);
```
