<?php
namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class FileManager
{
    public function fetchServerContents($params, $path, $queryPath)
    {
        $headers = explode('/',Str::after($queryPath,'/'));

        if(in_array($queryPath, $headers)){
           array_shift($headers);
        }

        $slash = $this->getSlashByOS();

        $directories = File::directories($path .  $slash . $params);
        $files =  File::files($path .  $slash . $params);
     
        $directoryContent = collect();
        foreach ($directories as $directory) {
            $directoryContent->push([
                'full_path' => $directory,
                'folder_name' => Str::afterLast($directory,  $slash),
                'type' => 'folder'
            ]);
        }

        $fileContents = collect();
        foreach ($files as $file) {
            // dd($file->getPathInfo());
            $fileContents->push([
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'pathName' => $file->getPathname(),
                'last_modified' => Carbon::parse($file->getMTime())->format('M d, Y , H:m:s'),
                'type' => 'file'
            ]);
        }

        $pathContents = collect($directoryContent)->merge($fileContents);

        return compact('pathContents', 'params', 'path','queryPath', 'headers');
    }

    public function storeFile($validatedFileData)
    {
        $content = json_decode($validatedFileData['content'],true);
        $pathName = $content['pathName'];
        $data = $validatedFileData['data'];
        
        try{
           
           return  File::put($pathName, $data);
            
        }catch(Exception $e){
            return false;
        }
    }
  

    public function createDirectory($validatedFileData)
    {
        $path = str_replace('~', $this->getSlashByOS(), $validatedFileData['path']);
        $directoryName =  $validatedFileData['new-directory-name'];

        try{
            mkdir($path . $this->getSlashByOS() . $directoryName);
            return true;
        }catch(Exception $e){
            return false;
        }

    }

   
    public function createFile($validatedFileData)
    {
        $path = str_replace('~', $this->getSlashByOS(), $validatedFileData['path']);
        $fileName =  $validatedFileData['new-file-name'];

        try{
            fopen('' . $path . $this->getSlashByOS() . $fileName . '', "w");
            return true;
        }catch(Exception $ex){
            return false;
        }

    }


    public function renameFile($validatedFileData)
    {
        $fullPath = str_replace('/',$this->getSlashByOS(), json_decode($validatedFileData['content'])->pathName);
        $path = Str::beforeLast($fullPath, $this->getSlashByOS());
        $newName =  $validatedFileData['rename-file-name'];
        
        try{
            rename($fullPath, $path. $this->getSlashByOS() .$newName);
            return true;
        }   catch(exception $e){
            return false;
        }
    }


    public function copyFile($validatedFileData){
     
        $decodedData = json_decode($validatedFileData['content']);
        $fullPath =  $decodedData->pathName;
        $fileName = $decodedData->filename;
        $ext = Str::afterLast($fileName, '.');
        $copyPath = $validatedFileData['copy-file-path'];
       
        $copyFullPath = $copyPath . $this->getSlashByOS() . $fileName;
        $renameFile = Str::beforeLast($fileName, '.') .'-1.' . $ext;
       

        if($fullPath == $copyFullPath){
            try{
                copy($fullPath, $copyPath. $this->getSlashByOS() . $renameFile); 
                return true;
            }
            catch(exception $e){
                return false;
            }

        }else{
            try{
                 copy($fullPath, $copyFullPath);
                 return true;
            }
            catch(exception $e){
                return false;
            }
        }
   
    }


    public function moveFile($validatedFileData){
        
        $decodedData = json_decode($validatedFileData['content']);
        $fullPath = str_replace('/', $this->getSlashByOS(),$decodedData->pathName);
        $fileName = $decodedData->filename;
        $movePath = str_replace('/',$this->getSlashByOS(),$validatedFileData['move-file-path']);


        if($fullPath != $movePath){
            try{
                rename($fullPath, $movePath . $this->getSlashByOS() . $fileName);
                return true;
               
            }
            catch(Exception $e){
                return false;
            } 
        }
        
    }

    public function getSlashByOS(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "\\";
        } 

        return "/";
    }
}


