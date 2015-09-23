### Set icons groups

Open `demo.html` and run the below steps in browser console.

1. Define javascript functions

    ```javascript
    var unycon = {
        groups: typeof window.unycon != 'undefined' && unycon.groups ? unycon.groups : {},
        icons: typeof window.unycon != 'undefined' && unycon.icons ? unycon.icons : {},
        includeJQuery: function () {
            if (typeof window.jQuery == 'undefined') {
                var script = document.createElement('script');
                script.src = 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js';
                document.body.insertBefore( script, document.body.firstChild );
            }
        },
        renderGroups: function () {
            var that = this;

            jQuery('.glyph .unycon + .mls').each(function(){
                var $icon = jQuery(this),
                    icon = jQuery.trim(jQuery(this).text()),
                    groups = typeof that.icons[icon] == 'undefined' ? {} : that.icons[icon].groups;

                var html = [];

                jQuery.each(that.groups, function(group, groupTitle){
                    html.push(''
                        + '<li>'
                        + '<label>'
                        + /**/'<input type="checkbox" value="'+ group +'" '+ (typeof groups[group] == 'undefined' ? '' : 'checked') +'> '
                        + /**/groupTitle
                        + '</label>'
                        + '</li>'
                    );
                });

                $icon.next('.icon-groups').remove(); // in case it already exists

                jQuery(
                    '<div class="icon-groups" data-icon="'+ icon +'" style="font-size:10px;clear:both;">'
                    +'<ul>'+ html.join('') +'</ul>'
                    +'</div>'
                )
                    .insertAfter($icon)
                    .on('change', 'input[type="checkbox"]', function(e){
                        var $checkbox = jQuery(this),
                            group = $checkbox.attr('value');

                        if (typeof that.icons[icon] == 'undefined') {
                            that.icons[icon] = {groups:{}};
                        }

                        if ($checkbox.prop('checked')) {
                            that.icons[icon].groups[group] = true;
                        } else {
                            delete that.icons[icon].groups[group];
                        }
                    });
            });
        }
    };
    ```

2. Include `jQuery`

    ```javascript
    unycon.includeJQuery();
    ```

3. Load groups

    ```javascript
    unycon.groups = {...}; // copy from index.html source code
    ```

4. Load icons

    ```javascript
    unycon.icons = {...}; // copy from index.html source code
    ```

5. Show checkboxes

    ```javascript
    unycon.renderGroups();
    ```

6. Set manually category checkboxes

7. Export icons

    ```javascript
    JSON.stringify(unycon.icons);
    ```
