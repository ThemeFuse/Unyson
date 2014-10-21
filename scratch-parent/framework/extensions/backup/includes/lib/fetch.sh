#!/bin/sh

wget -O srdb.class.php https://raw.githubusercontent.com/interconnectit/Search-Replace-DB/master/srdb.class.php

sed -i -e "s:@ini_set(\s*'memory_limit':// &:" srdb.class.php
