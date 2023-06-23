<?php

namespace App\Http\Controllers;

use App\Services\FileManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{

    /**
     * List all Server files and Directory contents.
     *
     * @OA\Get(
     *      path="/files",
     *      summary="List all files and Directory contents",
     *      tags={"FileManager"},
     *      description="List all all server contents.",
     * @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(
     *          response=200, 
     *          description="Successful request",
     *           @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                @OA\Property(
     *                    property="pathContents",
     *                    description="server content(files and directory)",
     *                    type="Files and Directories",
     *                    example="index.txt"
     *                ),
     *                  @OA\Property(
     *                    property="params'",
     *                    description="current directory name(s))",
     *                    type="Directory",
     *                    example="folder"
     *                ),
     *                @OA\Property(
     *                    property="path'",
     *                    description="File manager root path)",
     *                    type="Directory",
     *                    example="folder"
     *                ),
     *                   @OA\Property(
     *                    property=" $queryPath'",
     *                    description="Specific Server location)",
     *                    type="Directory",
     *                    example="folder"
     *                ),
     *                  
     *               ),
     * ),
     * ),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function index(FileManager $fileManager, $params = null)
    {
        $path = request('site-uuid');
        $queryPath = $_SERVER['QUERY_STRING'];

        extract($fileManager->fetchServerContents($params, $path, $queryPath));
       
        return view('file_manager.index', compact('pathContents', 'params', 'path','queryPath', 'headers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * store/save file Content
     *
     * @OA\Post(
     *      path="/files/store",
     *      summary="files and Directory contents",
     *      tags={"FileManager"},
     *      description="update or store to a server file.",
     * @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=201, description="Created request"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function store(Request $request, FileManager $fileManager)
    {
      $isStored =   $fileManager->storeFile(
                $request->validate([
                    'content' => 'required|json',
                    'data' => 'required'
                ])
    );
       
        if(!$isStored){
            return redirect()->back()->with('success','Failed to fail file Content.'); 
        }

        return redirect()->back()->with('success','File saved Successfully');
    }

      /**
     * View specific file content.
     *
     * @OA\Post(
     *      path="/files/show",
     *      summary="view files contents",
     *      tags={"FileManager"},
     *      description="view file manager file in readonly mode.",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful request"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function show()
    {
        $validated = request()->validate([
            'pathName' => 'required|string'
        ]);

        $pathName = $validated['pathName'];
        $ext = Str::afterLast($pathName, '.');

        if(! in_array($ext, ['jpg','png', 'jpeg', 'webm', 'flv', 'mp3','svg', 'WebP', 'mkv','gif','amv','3gp','flv','f4v','f4p','svi','f4a','f4b'])){
            return response()->json(['nonmedia'=> File::get($pathName)]);
        }else{
            return 'download_file_object/'. encrypt($pathName);
        }
       
    }

    public function showMediaFile($path){
        try{
            return  decrypt($path);
          }catch(Exception $ex){
              abort(404);
          }
    }

        /**
     * Edit View specific file content.
     *
     * @OA\Post(
     *      path="/files/edit",
     *      summary="Edit file contents",
     *      tags={"FileManager"},
     *      description="edit file manager file and update.",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful request"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function edit()
    {
         $validated = request()->validate([
            'pathName' => 'required|string'
        ]);
        
        $pathName = $validated['pathName'];
        $ext = Str::afterLast($pathName, '.');

        if(! in_array($ext, ['jpg','png', 'jpeg', 'webm', 'flv', 'mp3','svg', 'WebP', 'mkv','gif','amv','3gp','flv','f4v','f4p','svi','f4a','f4b'])){
            return File::get($pathName);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Delete  specific file content.
     *
     * @OA\Delete(
     *      path="/files/delete",
     *      summary="Edit file contents",
     *      tags={"FileManager"},
     *      description="delete file",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=204, description="No content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function destroy()
    {
        $validated = request()->validate([
            'pathName' => 'required|string'
        ]);
        
        $pathName = $validated['pathName'];
        return unlink($pathName);
    }

     /**
     * Download specific file content.
     *
     * @OA\Post(
     *      path="/files/download",
     *      summary="Download file contents",
     *      tags={"FileManager"},
     *      description="download file",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function download()
    {
        $validated = request()->validate([
            'pathName' => 'required|string'
        ]);
        
        $path = $validated['pathName'];
        return 'download_file_object/'. encrypt($path);
    }


    public function downloadObject($id)
    {
        try{
          return  response()->download(decrypt($id));
        }catch(Exception $ex){
            abort(404);
        }
    }

         /**
     * Create a directory on the Server
     *
     * @OA\Post(
     *      path="/files/create-directory",
     *      summary="create a new folder",
     *      tags={"FileManager"},
     *      description="create a new folder",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */

    public function createDirectory(FileManager $fileManager)
    {
        $validated = request()->validate([
            'path' => 'required',
            'new-directory-name' => 'required'
        ]);
       
        if(!$fileManager->createDirectory($validated)){
            return redirect()->back()->with('success','Failed to create Directory!');    
        };

        return redirect()->back()->with('success','Directory created successfully');

    }

    /**
     * Create a file on the Server
     *
     * @OA\Post(
     *      path="/files/create-file",
     *      summary="create a new file",
     *      tags={"FileManager"},
     *      description="create a new file",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=201, description="Created content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
    */
    public function createFile(FileManager $fileManager)
    {
        $validated = request()->validate([
            'path' => 'required',
            'new-file-name' => 'required'
        ]);

        if(!$fileManager->createFile($validated)){
            return redirect()->back()->with('success','Failed to create File'); 
        }
        return redirect()->back()->with('success','File created successfully');
        
    }



       /**
     * Rename a file on the Server
     *
     * @OA\Post(
     *      path="/files/rename-file",
     *      summary="Rename a new file",
     *      tags={"FileManager"},
     *      description="Rename a file",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=201, description="Created content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function renameFile(FileManager $fileManager)
    {
        $validated = request()->validate([
            'content' => 'required|json',
            'rename-file-name' => 'required'
        ]);
        
        if(!$fileManager->renameFile($validated)){
            return redirect()->back()->with('success','Failed to rename file ');
        }
            return redirect()->back()->with('success','File renamed Successfully');
    }

       /**
     * Copy a file in the Server
     *
     * @OA\Post(
     *      path="/files/copy-file",
     *      summary="copy a file to the same or different directory on the server",
     *      tags={"FileManager"},
     *      description="copy a file to the same or different directory on the server",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=201, description="Created content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */

    public function copy(FileManager  $fileManager){
     
        $validated = request()->validate([
            'content' => 'required|json',
            'copy-file-path' => 'required'
        ]);

        if(!$fileManager->copyFile($validated)){
            return redirect()->back()->with('success','Failed to copy File copied');
        }

        return redirect()->back()->with('success','File copied successfully');
       
       
    }


       /**
     * Move a file from one directory to another the Server
     *
     * @OA\Post(
     *      path="/files/move-file",
     *      summary="Move a file to a specific location on the server",
     *      tags={"FileManager"},
     *      description="create a new file",
     *      @OA\Parameter(in="header", required=false, name="application_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(in="query", required=false, name="site-uuid", @OA\Schema(type="string")),
     *      @OA\Response(response=201, description="Created content"),
     *      @OA\Response(response=422, description="Invalid payload"),
     *      @OA\Response(response=401, description="Unauthorized")
     * 
     * )
    */
    public function move(FileManager $fileManager){

        $validated = request()->validate([
            'content' => 'required|json',
            'move-file-path' => 'required'
        ]);

       if(!$fileManager->moveFile($validated)){
            return redirect()->back()->with('success','failed to move file successfully');
       }
        
        return redirect()->back()->with('success','File moved successfully');
    }


    
    public function getSlashByOS(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "\\";
        } 

        return "/";
    }
}
