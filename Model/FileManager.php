<?php

class FileManager
{
    public function scanDir($path)
    {
        $path = explode('/', $path);
        for ($i = 0; $i < sizeof($path); $i++) {
            $path[$i] = str_replace('.', '', $path[$i]); //ILLEGAL ACTION
        }
        $path = implode('/', $path);
        $dirPath = './Uploads/' . $_SESSION['id'];
        if ('' !== $path) {
            $dirPath = $dirPath . $path;
        }
        if (!is_dir($dirPath)) {
            return false;           //ILLEGAL ACTION
        }
        $result = scandir($dirPath);
        $dirs = [];
        $files = [];
        for ($i = 2; $i < sizeof($result); $i++) {
            if (is_dir($dirPath . '/' . $result[$i])) {
                $dirs[] = $result[$i];
            } else {
                $files[] = $result[$i];
            }
        }
        return [$dirs, $files];
    }

    public function download($path)
    {
        $path = explode('/', $path);
        for ($i = 0; $i < sizeof($path) - 1; $i++) {
            $path[$i] = str_replace('.', '', $path[$i]);
        }
        if ('..' == $path[sizeof($path) - 1]) {
            return false; //illegal action
        }
        $path = implode('/', $path);
        $path = 'Uploads/' . $_SESSION['id'] . $path;
        if (!is_file($path)) {
            return false; //illegal action
        }
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function rename($oldPath, $newPath)
    {
        if (is_dir($oldPath)) {
            $newPath = explode('/', $newPath);
            for ($i = 1; $i < sizeof($newPath); $i++) {
                $newPath[$i] = str_replace('.', '', $newPath[$i]);
            }
            $newPath[0] = '.';
            $newPath = implode('/', $newPath);
        }
        rename($oldPath, $newPath);
    }

    public function moove($oldPath, $newPath, $name)
    {
        $oldPath = './Uploads/' . $_SESSION['id'] . '/' . $oldPath;
        $newPath = './Uploads/' . $_SESSION['id'] . '/' . $newPath;
        if (!is_dir($oldPath) && !is_file($oldPath)){
            $errors[] = 'The file or folder you are trying to move does not exist';
            //ILLEGAL ACTION
        }
        if (!is_dir($newPath)){
            $errors[] = 'Destination folder is invalid';        //ILLEGAL ACTION
        }
        $newPath = $newPath . '/' . $name;
        if (is_dir($newPath) || is_file($newPath)){
            $errors[] = 'The file already exists at the destination';       //ILLEGAL ACTION
        }
        if (!empty($errors)){
            return $errors;
        }
        if (is_dir($oldPath)){
            $this->recurse_copy($oldPath, $newPath);
        } else {
            $this->rename($oldPath, $newPath);
        }
        return 'ok';
    }

    private function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
        $this->deleteDirs($src);
    }

    public function delete($delete)
    {
        $path = 'Uploads/' . $_SESSION['id'] . '/' . $delete;
        if (is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            $this->deleteDirs($path);
        } else {
            //ILLEGAL ACTION
        }
    }

    private static function deleteDirs($dir)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($dir);
    }
<<<<<<< HEAD

    public function securisePath($path)
    {
        $path = explode('/', $path);
        $path = array_filter($path);
        $path = array_values($path);
        for ($i = 0; $i < sizeof($path); $i++){
            if ('..' == $path[$i] || '.' == $path[$i]){
                unset($path[$i]);
            }
        }
        $path = array_values($path);
        $path = implode('/', $path);
        $path = str_replace('<', '', $path);
        $path = str_replace('>', '', $path);
        return $path;
    }
}
=======
    public function uploadFile($fileName, $file, $path)
    {
        if ('' == $fileName){
            $path = 'Uploads/' . $_SESSION['id'] . '/' . $path . '/' . $file['name'];
        } else {
            $path = 'Uploads/' . $_SESSION['id'] . '/' . $path . '/' . $fileName;
        }
        $errors = [];
        if (!$this->checkSize($file)) {
            $errors[] = 'The file is too large'; //ILLEGAL ACTION
        }
        if (!$this->fileExists($path)) {
            $errors[] = 'The file you tried to upload already exist'; //ILLEGAL ACTION
        }
        if (empty($errors)){
            move_uploaded_file($file['tmp_name'], $path);
            return 'ok';
        } else {
            return $errors;
        }
    }

    private function checkSize($file)
    {
        if (9999999 > $file['size']) {
            return true;
        } else {
            return false;
        }
    }

    private function fileExists($path)
    {
        if (file_exists($path)) {
            return false;
        } else {
            return true;
        }
    }
}
>>>>>>> 82431930c986df0ebdc97bb8cd464eadd4652b55
