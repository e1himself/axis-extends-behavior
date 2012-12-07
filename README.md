AxisExtendsBehavior
===================

This behavior allows you to declare single table inheritance entities with extended data fields
stored to another table.

Installation
------------

Use [Composer](http://getcomposer.org/). Just add this dependency to your `composer.json`:

```json
  "require": {
    "axis/axis-extends-behavior": "dev-master"
  }
```

Add behavior to your propel.ini file:
```ini
...
propel.behavior.axis_extends.class = lib.vendor.axis.axis-extends-behavior.lib.AxisExtendsBehavior
...
```

Usage
-----

### Declaration

Use behavior in your schema:
```yml
my_page:
  id: ~
  title: { type: varchar }
  body:  { type: varchar, size: 3000 }
  type:         { type: varchar }
  _inheritance:
    column:    type
    classes:
      default: axisPage

my_product_page_data:
  id: { primaryKey: true, type: integer, foreignTable: my_page, foreignReference: id, onDelete: cascade, required: true }
  product_id:  { type: integer, foreignTable: my_product, foreignReference: id, onDelete: restrict }
  _propel_behaviors:
    axis_extends: { class_name: MyProductPage, extends: my_page }
```

This schema will generate following classes:
```php
// main entity AR class
class MyPage extends BaseMyPage { /* ... */ }

// extended entity AR class
class MyProductPage extends BaseMyProductPage { /* ... */ }
// extended entity base class extends main entity class
class BaseMyProductPage extends MyPage { /* ... */ }

// extended entity additional fields object stored to `my_product_page_data`
class MyProductPageData extends BaseMyProductPageData { /* ... */ }
```

### Usage

```php
$myPage = new MyPage();
$myPage->setTitle('Regular page');
$myPage->setBody('Hello world!');
$myPage->save(); // stores 1 row to 'my_page' table

$productPage = new MyProductPage();
$productPage->setTitle('Product page'); // sets 'my_page.title' field
$productPage->setBody('It\'s a product page! Hooray!'); // sets 'my_page.body' field
// seamless extended fields access
$productPage->setProductId($product->getId()); // sets 'my_product_page_data.product_id' field
$productPage->save(); // stores 1 row to 'my_page' table and 1 row to 'my_product_page_data' table
```