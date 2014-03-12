#!/bin/sh

#  method.sh
#  
#
#  Created by leo on 08.04.13.
#
echo "new method name:"
read method
echo "in controller:"
read controller
repo=`pwd`
repo=$repo"/../lesapps"
fileController=$repo"/controllers/"$controller".php"
echo $fileController

#echo "create '"$method"' in controller '"$controller"'? (yes/no)"
#read resp
resp="yes"

if [ $resp="yes" ]
then
    if [ -f $fileController ]
    then
#sed -i -ne '/}/{x;s/^\n//;p;s/.*//;x};H;${g;s/\n\(.*\)}.*/\1/;p}' $fileController
        echo " \n\n\t/**\n\t*\n\t*\n\t*/\n\tpublic function $method() {\n\t\n\t}\n">>$fileController
        echo "<?php\n\n?>">$repo"/language/developpement/"$controller"/"$method"_lang.php"
        echo "{%extends \""$controller"/template/index.html.twig\"%}\n{%block content%}\n<h3>"$controller"/"$method"</h3>\n{%endblock%}">$repo"/themes/default/"$controller"/"$method".html.twig"
        echo "done."
    else
        echo "the controller don't exist Create it? (yes/no)"
        read resp
        if [ $resp="yes" ]
        then
            ./controller.sh
        fi
    fi
else
    echo "aborted."
fi
