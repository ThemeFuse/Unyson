### Update Icons

```javascript
// 1. Open http://fontawesome.io/icons/
// 2. Run this script
// 3. Convert JSON to PHP Array https://www.google.com/#q=json+to+php+online

var fa = {
	groups: {
		// 'id': 'Title'
	},
	icons: {
		// 'fa fa-adjust': {'group': 'web-app'},
	}
};

var $section = jQuery('section#web-application'),
	group;

do {
	group = {
		id: $section.attr('id'),
		title: $section.find('> h2').text()
	};

	fa.groups[group.id] = group.title;

	$section.find('.fontawesome-icon-list i.fa').each(function(){
		var icon = jQuery(this).attr('class');

		if (fa.icons[icon]) return;

		fa.icons[icon] = {group: group.id};
	});

	$section = $section.next();
} while($section.length);

console.log( JSON.stringify(fa) );
```