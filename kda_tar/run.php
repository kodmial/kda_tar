<?php

if ($key == 'archive') {

    $to_archive = ".";
    if ($handle = opendir($to_archive)) {
        $to_archive = array();
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $to_archive[] = $file;
            }
        }
        closedir($handle);
    }
    if ($gzip) {
        $archive .= $archive . ".gz";
        $tar_object = new Archive_Tar("$archive", true);
    } else {
        $tar_object = new Archive_Tar("$archive");
    }

    $tar_object->setErrorHandling(PEAR_ERROR_PRINT);
    $result = $tar_object->create($to_archive);
} elseif ($key == 'extract') {
    $destination = getcwd();
    $tar_object = new Archive_Tar("$file");
    $tar_object->setErrorHandling(PEAR_ERROR_PRINT);
    $result = $tar_object->extract("$destination");
} elseif ($key == 'unextract') {

    echo "NOT supported 'unextract' ";
} else {

    echo "NOT supported " . $key . " ";
}
?>
