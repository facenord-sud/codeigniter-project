#!/bin/sh

#  method2.sh
#  
#
#  Created by leo on 20.04.13.
#

echo "new method name: "
read methodName
echo "in controller: "
read controllerName
repo=`pwd`
repo=$repo"/../lesapps"
echo "create '"$methodName"()' in controller '"$repo"/controllers/'"$controllerName".php? (yes/no)"
read resp
noController=1

if [ $resp = "no" ]
then
noController=0
echo "create file and dir structure for the controller name? (yes/no)"
read resp
fi

if [ -f $repo"/controllers/"$controllerName".php" ]
then
echo "the controller already exist. Continue? (yes/no)"
read resp
fi

if [ $resp = "yes" ]
then
if [ $noController != 0 ]
then
touch $repo"/controllers/"$controllerName".php"
fi
echo "write short description of the controller"
read description
mkdir -v $repo"/language/developpement/"$controllerName
mkdir -v $repo"/language/developpement/"$controllerName"/template"
touch $repo"/language/developpement/"$controllerName"/template/template_lang.php"
mkdir -v $repo"/themes/default/"$controllerName
mkdir -v $repo"/themes/default/"$controllerName"/template"
touch $repo"/themes/default/"$controllerName"/template/index.html.twig"
echo "<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n/**\n * $description\n * \n * @author $USER\n */\nclass "$controllerName "extends MY_Controller {\n\n}\n?>">$repo"/controllers/"$controllerName".php"
echo "<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n\n?>">$repo"/language/developpement/"$controllerName"/template/template_lang.php"
echo "{%extends _layout%}">$repo"/themes/default/"$controllerName"/template/index.html.twig"
echo "done."
else
echo "aborted"
fi