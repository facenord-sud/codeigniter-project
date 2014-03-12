<?php

echo "\n\nBienvenue.\nCe script permet la création de nouvelle page.\n\nNom de la nouvelle page:\n";
$namePage = rtrim(fgets(STDIN));
echo "\nLe nom est  : $namePage\nDans quel module?\n";
$nameModule = rtrim(fgets(STDIN));
$dir = '../lesapps/';
$dirController = $dir . 'controllers/';

$controllers = scandir($dirController);
$isModule = false;
foreach ($controllers as $controller) {
    if ($controller == $nameModule . '.php') {
        $isModule = TRUE;
        break;
    }
}

if (!$isModule) {
    echo "\nle module n'existe pas. Le créer? (oui/non)\n";
    if (rtrim(fgets(STDIN)) == 'non') {
        echo "\n\nbye\n\n";
    } else {
        touch($dirController . $nameModule . '.php');
        $handle = fopen($dirController . $nameModule . '.php', 'rw+');
        if (!$handle) {
            echo "Erreur lecture fichier\n";
            exit();
        }
        $handle_part1 = fopen('./controller_type_1.php', 'rw+');
        if (!$handle_part1) {
            echo "Erreur lecture fichier\n";
            exit();
        }
        $handle_part2 = fopen('./controller_type_2.php', 'rw+');
        if (!$handle_part2) {
            echo "Erreur lecture fichier\n";
            exit();
        }
//        $controller_type = "if (!defined('BASEPATH'))\nexit('No direct script access allowed');\n\nclass Document extends MY_Controller {\n/**\n* Page d'index pour le documentController\n*\n* \n*/\npublic function index() {\necho 'doc';\n}\n}\n";
        fwrite($handle, "<?php\n");
        while (!feof($handle_part1)) {
            $buffer = fgets($handle_part1);
            fwrite($handle, $buffer);
        }
        fwrite($handle, ' '.$nameModule.' ');
        while (!feof($handle_part2)) {
            $buffer = fgets($handle_part2);
            fwrite($handle, $buffer);
        }
        fclose($handle_part1);
        fclose($handle_part2);
    }
}

//$handle = fopen($dirController . $nameModule . '.php', 'rw');
if (!$handle) {
    echo "Erreur lecture fichier\n";
    exit();
}
$hasMethod = FALSE;
while (!feof($handle)) {
    $buffer = fgets($handle);
    if (preg_match('`public function ' . $namePage . '`', $buffer)) {
        $hasMethod = TRUE;
        break;
    }
}

if ($hasMethod) {
    echo "\nDéslolé, le nom existe déjà. trouvez-en un autre\n";
    exit();
}

$controller_content = file_get_contents($dirController . $nameModule . '.php');
$controller_modified = substr($controller_content, 0, strrpos($controller_content, "}"));
echo $controller_modified;
//ftruncate($handle, 0);
//fwrite($handle, $controller_modified);

//echo "combien de paramètre ? \n";
//$nubParam = rtrim(fgets(STDIN));
//$params = '';
//if ($nubParam > 0) {
//    
//}

$method_type = "\npublic function $namePage(" . substr($params, 0, strlen($params) - 2) . ") {\necho '$namePage';\n}\n}";
//fwrite($handle, $method_type);
fclose($handle);

echo "\nTout c'est bien passé. Ciao!\n\n";
?>
