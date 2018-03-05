<?php

class FileManager
{
    public function scanDir($path)
    {
        $path = explode('/', $path);
        for ($i = 0; $i < sizeof($path); $i++){
            $path[$i] = str_replace('.', '', $path[$i]); //ILLEGAL ACTION
        }
        $path = implode('/', $path);
        $dirPath = './Uploads/' . $_SESSION['id'];
        if ('' !== $path){
            $dirPath = $dirPath . $path;
        }
        if (!is_dir($dirPath)){
            return false;           //ILLEGAL ACTION
        }
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
        $path = explode('/', $path);
        for ($i = 0; $i < sizeof($path) - 1; $i++){
            $path[$i] = str_replace('.', '', $path[$i]);
        }
        if ('..' == $path[sizeof($path) - 1]){
            return false; //illegal action
        }
        $path = implode('/', $path);
        $path = 'Uploads/' . $_SESSION['id'] . $path;
        if (!is_file($path)){
            return false; //illegal action
        }
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function rename($oldPath, $newPath)
    {
        if (is_dir($oldPath)){
            $newPath = explode('/', $newPath);
            for ($i = 1; $i < sizeof($newPath); $i++){
                $newPath[$i] = str_replace('.', '', $newPath[$i]);
            }
            $newPath[0] = '.';
            $newPath = implode('/', $newPath);
            var_dump($newPath);
        }
        rename($oldPath, $newPath);
    }
}