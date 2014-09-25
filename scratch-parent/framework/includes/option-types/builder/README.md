## Create builder

#### In PHP

* Create class for items

```php
abstract class FW_Option_Type_Builder_Foo_Item extends FW_Option_Type_Builder_Item
{
	final public function get_builder_type()
	{
		return 'builder-foo';
	}

	// ...
}
```

* Create builder option type

```php
class FW_Option_Type_Builder_Foo extends FW_Option_Type_Builder
{
	final public function get_type()
	{
		return 'builder-foo';
	}

	final public function item_type_is_valid($item_type_instance)
	{
		if (is_subclass_of($item_type_instance, 'FW_Option_Type_Builder_Foo_Item')) {
			return true;
		}

		return false;
	}

	// ...
}

FW_Option_Type::register('FW_Option_Type_Builder_Foo');
```

#### In Javascript

```javascript
// If you want to change Builder class for your type, attach to the event
fwEvents.trigger('fw-builder:'+ 'builder-foo' +':before-create', function(data){
	var MyBuilder = data.Builder.extend({
		// ...
	});

	data.Builder = MyBuilder;
});
```

## Register item to builder

#### In PHP

```php
class FW_Option_Type_Builder_Foo_Item_Bar extends FW_Option_Type_Builder_Foo_Item
{
	public function get_builder_type()
	{
		return 'builder-foo';
	}

	public function get_type()
	{
		return 'bar';
	}

	public function enqueue_static()
	{
		wp_enqueue_style(
			'fw-builder-'. $this->get_builder_type() .'-item-'. $this->get_type(),
			fw_get_framework_directory_uri('/.../items/'. $this->get_type() .'/static/css/styles.css')
		);

		wp_enqueue_script(
			'fw-builder-'. $this->get_builder_type() .'-item-'. $this->get_type(),
			fw_get_framework_directory_uri('/.../items/'. $this->get_type() .'/static/js/scripts.js'),
			array(
				'fw-events',
			),
			false,
			true
		);

		// ...
	}

	// ...
}

FW_Option_Type_Builder::register_item_type('FW_Option_Type_Builder_Foo_Item_Bar');
```

#### In JavaScript

Attach to event and register your item **class** (not instance!)

```javascript
fwEvents.one('fw-builder:'+ 'builder-foo' +':register-items', function(builder){
	/** Minimal example: only required parameters */
	{
		var MyItem = builder.classes.Item.extend({
			defaults: {
				// required: unique type of the item (within the builder)
				type: 'my-unique-item-type'
			}
		});
	}

	/** Full example: all possibilities */
	{
		var MyItem = builder.classes.Item.extend({
			defaults: {
				type: 'my-unique-item-type'
				// other item data ...
			},
			initialize: function(){
				this.defaultInitialize();

				this.view = new builder.classes.ItemView({
					id: 'fw-visual-builder-item-'+ this.cid,
					model: this,
					template: _.template('<div>...</div>')
				});
				// Also you can extend builder.classes.ItemView and create a view with more advanced functionality
			}
		});
	}

	builder.registerItemClass(MyItem);
});
```