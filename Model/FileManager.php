<?php

class FileManager
{
    public function scanDir($id, $path = false)
    {
        $dirPath = 'Uploads/' . $id;
        $result = scandir($dirPath);
        $dirs = [];
        $files = [];
        for ($i = 2; $i < sizeof($result); $i++){
            if (is_dir($dirPath . '/' . $result[$i])){
                $dirs[] = $result[$i];
            } else {
                $files[] = $result[$i];
            }
        }
        return [$dirs, $files];
    }

    public function download($path){
        $path = 'Uploads/' . $_SESSION['id'] . $path;
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }


}