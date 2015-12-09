<?php
$delete_password	=	'ENETER YOUR DELETE PASSWORD';
require_once 'kda_tar/PEAR.php';
include_once 'kda_tar/PEAR5.php';
require_once 'kda_tar/Tar.php';
$nDir = opendir(".");
while (false !== ( $file = readdir($nDir) )) {
    $fileinfo = pathinfo($file);
    if ($file != "." && $file != ".." && $fileinfo['extension'] !== "tar" && $file !== $_SERVER['SCRIPT_FILENAME']) {
        $count_files = $count_files + 1;
    }
}
$file = $_POST["file"];
$key = $_POST["key"];
$cmd = $_POST["cmd"];
$delete = $_POST["delete"];
$start_delete = $_POST["start_delete"];

$array = explode("/", $_SERVER['SCRIPT_FILENAME']);
$array_id = count($array) - 1;
$this_file = $array["$array_id"];

$not_delete['files'] = array($this_file);
$not_delete['dirs'] = array('kda_tar');
$gzip = false;

if (!function_exists('shell_exec')) {
    echo "<p style='border:1px solid red; padding:15px;'>'shell_exec' not supported</p>";
}
echo"<form style='border:3px solid #ccc; padding:15px;' name='extract' action='" . $_SERVER['PHP_SELF'] . "' method='post'>
	    		<input style='padding:10px;' type='text' name='file' value='" . $_SERVER['SCRIPT_FILENAME'] . "'>
	    		<input type='submit' name='key' value='archive'>
	    		</form>";

if (empty($key) and empty($delete)) {
    $result = list_file(".");

    for ($i = 0; $i < count($result); $i++) {
        $fileinfo = pathinfo($result[$i]);
        if ($fileinfo['extension'] == "tar") {
            echo"
	    		<form style='border:3px solid black; padding:15px;' name='extract' action='" . $_SERVER['PHP_SELF'] . "' method='post'>
	    		<input style='padding:10px;' type='text' name='file' value='" . $fileinfo['basename'] . "'>
	    		<input style='padding:10px;' type='text' name='cmd' value='tar -xf " . $fileinfo['basename'] . " &'>
	    		<input type='submit' name='key' value='extract'>
	    		</form>";
        } elseif ($fileinfo['extension'] == "extracted") {
            echo"
	    		<form style='border:3px solid #ccc; padding:15px;' name='extract' action='" . $_SERVER['PHP_SELF'] . "' method='post'>
	    		<input style='padding:10px;' type='text' name='file' value='" . $fileinfo['basename'] . "'>
	    		<input type='submit' name='key' value='unextract'>
	    		</form>";
        }
    }

    echo"<h4>DELETE ALL " . $count_files . " files and dirictories</h4>
	    		<form style='border:1px solid red; padding:15px;' name='extract' action='" . $_SERVER['PHP_SELF'] . "' method='post'>
	    		<input type='submit' name='delete' value='ok'>
	    		</form>";
} elseif (!empty($delete)) {
    echo"<h3>DELTE " . $count_files . " files and dirictories?</h3>
	    		<form style='border:5px solid red; padding:15px;' name='extract' action='" . $_SERVER['PHP_SELF'] . "' method='post'>
	    		<input style='padding:10px; border:3px solid black' type='text' name='delete' value=''>
	    		<input style='border:5px solid red;' type='submit' name='start_delete' value='run'>
	    		</form>";
    if ($delete == $delete_password) {
        $count_delete = delete_other();
        echo"<h1>[DELETE ALL] =" . $count_delete . "= OK</h1>";
    }

    echo"<hr/><a href='" . $_SERVER['PHP_SELF'] . "'>LIST FILES</a>";
} else {

    echo"<div style='border:3px solid blue; padding:15px;'>";

    if ($key == "extract" and file_exists($file)) {

        if (!function_exists('shell_exec')) {
            include('kda_tar/run.php');
        } elseif (!empty($cmd)) {
            $result = shell_exec($cmd);
        }

        if ($result) {
            print_r($result);
        } else {
            print_r($result);
            echo "<hr/>";
            echo"<h1>NOT RESULT [$result]</h1>" . $cmd;
        }
        if (rename($file, $file . ".extracted")) {
            echo"<h4>REname extracted file - OK</h4>";
        } else {
            echo"<h4>ERROR REname file</h4>";
        }
        echo"<hr/><a href='" . $_SERVER['PHP_SELF'] . "'>LIST FILES</a>";
    } elseif ($key == "unextract" and file_exists($file)) {

        $newname = str_replace(".extracted", "", $file);
        if (rename($file, $newname)) {
            echo"<h4>" . $file . "</h4> UNextract OK  newname:($newname)";
        } else {
            echo"<h1>ERROR</h1>";
        }
        echo"<hr/><a href='" . $_SERVER['PHP_SELF'] . "'>LIST FILES</a>";
    } elseif ($key == "archive") {

        $archive = date('Y-m-d_h-i-s') . ".tar";

        echo "<h1 color='red'>START ARCHIVE</h1>";
        echo "<h4>KEY:" . $key . "</h4><h4>FILE:" . $file . "</h4>";

        if (!function_exists('shell_exec')) {
            include('kda_tar/run.php');
        } else {
            if ($gzip) {
                $archive = $archive . ".gz";
                $cmd = "tar -czf " . $archive . " *";
            } else {
                $cmd = "tar -cf " . $archive . " *";
            }


            $result = shell_exec($cmd);
        }

        print_r($result);
        echo "<hr/>";
        echo"<a href='$archive'>$archive</a>";
        echo"<hr/><a href='" . $_SERVER['PHP_SELF'] . "'>LIST FILES</a>";
    } else {
        echo "<h1 color='red'>ERROR</h1>";
        echo "<h4>KEY:" . $key . "</h4><h4>FILE:" . $file . "</h4>";
    }
    echo"</div>";
}

function list_file($dir) {
    if ($dir [strlen($dir) - 1] != '/') {
        $dir .= '/'; //добавляем слеш в конец если его нет
    }

    $nDir = opendir($dir);

    while (false !== ( $file = readdir($nDir) )) {

        if ($file != "." && $file != "..") {
            if (!is_dir($dir . $file)) {//если это не директория
                $files [] = $file;
            }
        }
    }
    closedir($nDir);
    return $files;
}

function delete_other() {
    global $not_delete;
    $dir = ".";

    if ($dir [strlen($dir) - 1] != '/') {
        $dir .= '/'; //добавляем слеш в конец если его нет
    }

    $nDir = opendir($dir);

    while (false !== ( $file = readdir($nDir) )) {

        $fileinfo = pathinfo($file);
        if ($file != "." and $file != ".." and $fileinfo['extension'] != "tar" and !in_array($fileinfo['basename'], $not_delete['files']) and !in_array($fileinfo['basename'], $not_delete['dirs'])) {

            if (!rmdirr($file) and !is_file($file)) {
                echo"<h3>START chmod:" . $file . "</h3>";
                $cmd = "chmod -R 777" . $file;
                $result = shell_exec($cmd);
                chmod($file, 0777);
                rmdirr($file);
            }
            $count_delete = $count_delete + 1;
        }
    }
    closedir($nDir);
    return $count_delete;
}

function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);

    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        rmdirr("$dirname/$entry");
    }
    $dir->close();
    return rmdir($dirname);
}

?>