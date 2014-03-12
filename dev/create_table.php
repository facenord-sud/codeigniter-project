<?php

$queries = array();
$dirEntity = $_SERVER['argv'][1];
define('APPPATH', '../lesapps/');

echo $_SERVER['argv'][1];

if (!is_dir($dirEntity)) {
    echo "Desole ce n'est pas un dossier\n";
    return;
}

define('BASEPATH', 0);

require '../lesapps/config/database.php';
try {
    $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $pdo = new PDO('mysql:host=' . $db['default']['hostname'] . ';dbname=' . $db['default']['database'] . '', $db['default']['username'], $db['default']['password'], $pdo_options);
} catch (Exception $e) {
    echo "Une erreure est survenue pendant la connection à la bdd. Vérifier vos paramètres";
    return;
}

$dir = scandir($dirEntity);
$numEntity = 0;
$namesEntity = "";

foreach ($dir as $file) {
    echo "$file\n";
    if (preg_match("`_entity.php?$`", $file)) {

        $entity = substr(ucfirst($file), 0, strlen($file) - 4);
        require_once $dirEntity . '/' . $file;

        try {
            $ref = new ReflectionClass($entity);
            $attributs = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
        } catch (Exception $exc) {
            echo "$entity n'est pas une classe\n";
            continue;
        }

        $attrsAnnot = array();

        require_once '../dev/addendum/dbb/Mysql_annot.php';
        foreach ($attributs as $attr) {
            try {
                $annot = new ReflectionAnnotatedProperty($entity, $attr->getName());
                $isNull = '';
                if ($annot->getAnnotation('NotNull')->value) {
                    $isNull = "NOT NULL";
                }
                $attrsAnnot[] = array(
                    $annot->getAnnotation('Type')->value,
                    $isNull,
                    $annot->getAnnotation('Key')->value,
                    $annot->getAnnotation('DefaultValue')->value,
                    $annot->getAnnotation('Extra')->value
                );
            } catch (Exception $exc) {
                echo "Impossible d'utiliser les annontations pour la table $entity\n";
                break;
            }
        }



        $table = preg_replace('`_entity.php?$`', '', $file);
        $isUpToDate = TRUE;

        $sql = "SHOW TABLES LIKE '$table'";
        $req = $pdo->query($sql);
        $res = $req->fetch();
        if (!empty($res)) {
            echo "$table: table trouvée\n\n";
            $sql = "DESCRIBE $table";
            $req = $pdo->query($sql);
            $fields = $req->fetchAll(PDO::FETCH_ASSOC);
            foreach ($fields as $key => $field) {
                if ($field['Field'] != $attributs[$key]->getName()) {
                    $isUpToDate = FALSE;
                    break;
                }
            }
        } else {
            echo "$table: table pas à jour\n\n";
            $isUpToDate = FALSE;
            $mtm = TRUE;
        }

        $privateProperties = $ref->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($privateProperties as $property) {
            $tableProperty = $property->getName();
            $annot = new ReflectionAnnotatedProperty($entity, $tableProperty);
            if ($annot->getAnnotation('Relation')->value == "MTM") {
                $res = $pdo->query("SHOW TABLES LIKE '$tableProperty\_$table'")->fetch();
                if (empty($res)) {
                    $isUpToDate = FALSE;
                    $queries[] = "CREATE TABLE $tableProperty" . "_" . "$table (id_$table int(11), id_$tableProperty int(11))";
                    $numEntity++;
                    $mtm=TRUE;
                }
            }
            
        }

        if ($isUpToDate) {
            $namesEntity = $namesEntity . "La table $entity est à jour\n";
            continue;
        } else {
            $tableFields = '';
            foreach ($attributs as $key => $attr) {
                $tableFields = $tableFields . $attr->getName() . ' ' . $attrsAnnot[$key][0] . ' ' . $attrsAnnot[$key][1] . ' ' . $attrsAnnot[$key][2] . ' ' . $attrsAnnot[$key][3] . ' ' . $attrsAnnot[$key][4] . ', ';
            }


            $queries[] = "CREATE TABLE $table (" . substr($tableFields, 0, strlen($tableFields) - 2) . ");";
            echo $sql . "\n";
            $numEntity++;
            $namesEntity = $namesEntity . "  " . $entity . "\n";
        }
    }
}
$isUpToDate = FALSE;
if ($isUpToDate) {
    echo "Toutes les tables sont à jour\n";
    return;
}
if ($numEntity == 0) {
    echo "Desole ce dossier ne contient pas d'entity respectant les règles de nommage.\n";
    return;
}

echo "\n\nBienvenue, le script permet de generer les tables correspondantes aux entites d'un dossier\n";
echo "le dossier que vous avez indique est : " . $dirEntity . "\nIl contient $numEntity entity pas à jour:\n$namesEntity Voulez-
        vous le(s) mettres à jour ? (yes/no)";
if (rtrim(fgets(STDIN)) == 'yes') {
    foreach ($queries as $sql) {
        echo $sql . "\n";
        $pdo->query($sql);
    }

    echo "\nc tout bon. \nau revoir.";
} else {
    echo "\nok. Au revoir\n";
}

echo "\n";
?>