<?php

require_once('Model/LogManager.php');

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
        $path = './uploads/' . $_SESSION['id'] . $path;
        if (!is_file($path)) {
            return false; //illegal action
        }

        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        } else {
            //illegal ACTION
        }
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
        $logManager = new LogManager();
        $oldPath = './Uploads/' . $_SESSION['id'] . '/' . $oldPath;
        $newPath = './Uploads/' . $_SESSION['id'] . '/' . $newPath;
        if (!is_dir($oldPath) && !is_file($oldPath)){
            $errors[] = 'The file or folder you are trying to move does not exist';
            $logManager->writeToLog('try to move a file who does not exist-> ' . $oldPath);
        }
        if (!is_dir($newPath)){
            $errors[] = 'Destination folder is invalid';
            $logManager->writeToLog('try to move a file with a invalid destination-> ' . $newPath);
        }
        $newPath = $newPath . '/' . $name;
        if (is_dir($newPath) || is_file($newPath)){
            $errors[] = 'The file already exists at the destination';
            $logManager->writeToLog('try to move a file who already exist a destination-> ' . $newPath);
        }
        if (!empty($errors)){
            return $errors;
        }
        if (is_dir($oldPath)){
            $logManager->writeToLog('move a folder ' . $oldPath .' -> ' . $newPath, false);
            $this->recurse_copy($oldPath, $newPath);
        } else {
            $logManager->writeToLog('move a file ' . $oldPath . ' -> ' . $newPath, false);
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
        $logManager = new LogManager();
        $path = 'Uploads/' . $_SESSION['id'] . '/' . $delete;
        if (is_file($path)) {
            $logManager->writeToLog('delete file-> ' . $path, false);
            unlink($path);
        } elseif (is_dir($path)) {
            $logManager->writeToLog('delete folder ' . $path, false);
            $this->deleteDirs($path);
        } else {
            $logManager->writeToLog('try to delete something who does not exist-> ' . $path);
        }
    }

    private static function deleteDirs($dir)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($dir);
    }

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

    public function uploadFile($fileName, $file, $path)
    {
        $logManager = new LogManager();
        if ('' == $fileName){
            $path = 'Uploads/' . $_SESSION['id'] . '/' . $path . '/' . $file['name'];
        } else {
            $path = 'Uploads/' . $_SESSION['id'] . '/' . $path . '/' . $fileName;
        }
        $errors = [];
        if (!$this->checkSize($file)) {
            $errors[] = 'The file is too large';
            $logManager->writeToLog('Try to upload a file to large');
        }
        if (!$this->fileExists($path)) {
            $errors[] = 'The file you tried to upload already exist';
            $logManager->writeToLog('try to upload a file who already exist');
        }
        if (empty($errors)){
            move_uploaded_file($file['tmp_name'], $path);
            $logManager->writeToLog('Upload-> ' . $path, false);
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

    public function checkExt($allFiles)
    {
        $writeExt = ['html', 'css', 'js', 'php', 'go', 'log', 'txt', 'rm'];
        $imgExt = ['jpg', 'png'];
        $audioExt = ['ogg', 'mp3', 'wav'];
        $videoExt = ['mp4', 'gif', 'avi'];
        $files = [];
        for ($i = 0; $i < sizeof($allFiles); $i++){
            $files[$i]['name'] = $allFiles[$i];
            for ($j = 0; $j < sizeof($writeExt); $j++){
                $fileExt = pathinfo($allFiles[$i], PATHINFO_EXTENSION);
                if ($writeExt[$j] == $fileExt){
                    $files[$i]['write'] = 'ok';
                }
            }
            for ($j = 0; $j < sizeof($imgExt); $j++){
                $fileExt = pathinfo($allFiles[$i], PATHINFO_EXTENSION);
                if ($imgExt[$j] == $fileExt){
                    $files[$i]['img'] = 'ok';
                }
            }
            for ($j = 0; $j < sizeof($audioExt); $j++){
                $fileExt = pathinfo($allFiles[$i], PATHINFO_EXTENSION);
                if ($audioExt[$j] == $fileExt){
                    $files[$i]['audio'] = 'ok';
                }
            }
            for ($j = 0; $j < sizeof($videoExt); $j++){
                $fileExt = pathinfo($allFiles[$i], PATHINFO_EXTENSION);
                if ($videoExt[$j] == $fileExt){
                    $files[$i]['video'] = 'ok';
                }
            }
        }
        return $files;
    }

}
