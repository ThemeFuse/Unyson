#!/bin/bash

ACCOUNT='ThemeFuse'

EXTENSIONS='
blog:Unyson-Blog-Extension
update:Unyson-Update-Extension
shortcodes:Unyson-Shortcodes-Extension
shortcodes/extensions/page-builder:Unyson-PageBuilder-Extension
learning:Unyson-Learning-Extension
sidebars:Unyson-Sidebars-Extension
feedback:Unyson-Feedback-Extension
breadcrumbs:Unyson-Breadcrumbs-Extension
media:Unyson-Empty-Extension
media/extensions/slider:Unyson-Sliders-Extension
media/extensions/population-method:Unyson-PopulationMethods-Extension
styling:Unyson-Styling-Extension
analytics:Unyson-Analytics-Extension
megamenu:Unyson-MegaMenu-Extension
portfolio:Unyson-Portfolio-Extension
seo:Unyson-SEO-Extension
backups:Unyson-Backups-Extension
events:Unyson-Events-Extension
builder:Unyson-Builder-Extension
forms:Unyson-Forms-Extension
mailer:Unyson-Mailer-Extension
social:Unyson-Social-Extension
translation:Unyson-Translation-Extension
shortcodes/extensions/wp-shortcodes:Unyson-WP-Shortcodes-Extension
'

cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )" # script dir

echo "$EXTENSIONS" | grep -v '^$' | while read line; do
    DIR=`echo "$line" | cut -d: -f1`
    REP=`echo "$line" | cut -d: -f2`

    echo ""

    if [ -d "$DIR" ]; then
        echo "[Warning] $DIR already exists. Skipping."
    else
        if [[ $REP == *"/"* ]]
        then
            COMMAND="git clone git@github.com:${REP}.git $DIR"
        else
            COMMAND="git clone git@github.com:${ACCOUNT}/${REP}.git $DIR"
        fi
        echo "$COMMAND"
        $COMMAND
    fi
done
