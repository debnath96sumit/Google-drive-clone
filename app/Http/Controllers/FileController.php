<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\File;
use App\Models\User;
use App\Models\FileShare;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\StarredFile;
use Illuminate\Http\Request;
use App\Http\Resources\FileResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TrashFileRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\FileShareRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\FilesActionRequest;
use App\Mail\ShareFileMail;
use Illuminate\Support\Facades\Mail;

class FileController extends Controller
{
    public function myFiles(Request $request,  string $folder = null)
    {
        if ($folder) {
            $folder = File::query()->where('created_by', Auth::id())
                                   ->where('path', $folder)
                                   ->firstOrFail();
        }
        if (!$folder) {
            $folder = $this->getRoot();
        }
        $favourites = (int)$request->get('favourites');
        $search = $request->get('search');
        
        $query = File::query()->select('files.*')
                              ->with('starred')
                              ->where('created_by', Auth::id())
                              ->whereNot('_lft', 1)
                              ->orderBy('is_folder', 'desc')
                              ->orderBy('files.created_at', 'desc')
                              ->orderBy('files.id', 'desc');

        if ($search) {
            $query->where('name', 'like',  '%'.$search.'%');
        } else {
            $query->where('parent_id', $folder->id);
        }
        
        if ($favourites == 1) {
            $query->join('starred_files', 'starred_files.file_id', 'files.id')
                  ->where('starred_files.user_id', Auth::id());
        }
        $files = $query->paginate(10);
        
        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }
        
        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);
        
        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
    }

    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        
        $parent = $request->parent;
        
        if (!$parent) {
            $parent = $this->getRoot(); 
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];
        // $file->save();

        $parent->appendNode($file);

    }

    public function fileStore(StoreFileRequest $request)
    {
        $data = $request->validated();
        $file_tree = $request->file_tree;
        $parent = $request->parent;
        $user = $request->user();

        if (!$parent) {
            $parent = $this->getRoot();
        }

        if (!empty($file_tree)) {
            $this->saveFileTree($file_tree, $parent, $user);
        }else {
            foreach ($data['files'] as $key => $file) {
                $this->saveFile($file, $parent, $user);
            }
        }
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()
                            ->where('created_by', Auth::id())
                            ->firstOrFail();
    }

    public function saveFileTree($file_tree, $parent, $user)
    {
        foreach ($file_tree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = true;
                $folder->name = $name;
                
                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            }else {
                $this->saveFile($file, $parent, $user);
            }
        }
    }

    public function destroy(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        
        if ($data['all']) {
            $children = $parent->children;
            foreach ($children as $child) {
                $child->moveToTrash();
            }
        }else {
            foreach ($data['ids'] ?? [] as $id) {
                $file = File::find($id);
                if ($file) {
                    $file->moveToTrash();
                }
            }
        }
        
        return to_route('myFiles', ['folder' => $parent->path]);
    }

    public function download(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }
        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select a file to download' 
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $file_name = $parent->name . '.zip';
        }else {
            [$url, $file_name] = $this->getDownloadUrl($ids, $parent->name);
        }

        return [ 
            'url' => $url,
            'file_name' => $file_name
        ];
    }

    public function createZip($files)
    {
        $zipPath = "zip/" . Str::random() . '.zip';
        $publicPath = $zipPath;

        if (!is_dir(dirname($publicPath))) {
            Storage::disk('public')->makeDirectory(dirname($publicPath));
        }
        $zipFile =  Storage::disk('public')->path($publicPath);
        
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }
        $zip->close();

        return asset(Storage::url($zipPath));
    }

    public function trash(Request $request) 
    {
        $search = $request->get('search');
        $trashed_files = File::onlyTrashed()
                             ->where('created_by', Auth::id())
                             ->orderBy('is_folder', 'desc')
                             ->orderBy('deleted_at', 'desc');
        if ($search) {
            $trashed_files->where('name', 'like',  '%'.$search.'%');
        }
        $trashed_files = $trashed_files->paginate(10);

        $files = FileResource::collection($trashed_files);
        if ($request->wantsJson()) {
            return $files;
        }
        return Inertia::render('trash', compact('files'));
    }

    public function restore(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();

            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()
                            ->whereIn('id', $ids)
                            ->get();

            foreach ($children as $child) {
                $child->restore();
            }
        }

        return to_route('trash');
    }

    public function deleteForever(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();

            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()
                            ->whereIn('id', $ids)
                            ->get();

            foreach ($children as $child) {
                $child->deleteForever();
            }
        }

        return to_route('trash');
    }

    public function addToFavourites(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }
        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select a file to add' 
            ];
        }

        if ($all) {
            $children = $parent->children;
        }else{
            $children = File::find($ids);
        }

        $data = [];

        foreach ($children as $child) {
            $data[] = [
                'file_id' => $child->id,
                'user_id' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        
        StarredFile::insert($data);

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    public function starredFiles($file_id)
    {
        try {
            $starred = StarredFile::where('file_id', $file_id)
                                  ->where('user_id', Auth::id()) 
                                  ->first();
            if ($starred) {
                $starred->delete();
            }else {
                StarredFile::create([
                    'file_id' => $file_id,
                    'user_id' => Auth::id(),
                ]);
            }

            return response()->json([
                'success' => true
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function shareMedia(FileShareRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        
        if (!$parent) {
            $parent = $this->getRoot();
        }
        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        $email = $data['email'];
        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select a file to add' 
            ];
        }
        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            return redirect()->back();
        }

        if ($all) {
            $files = $parent->children;
        }else {
            $files = File::find($ids);
        }

        $ids = Arr::pluck($files, 'id');

        $existingSharedFiles = FileShare::query()->whereIn('file_id', $ids)
                                                 ->where('user_id', $user->id)
                                                 ->pluck('file_id')
                                                 ->toArray();
        $data = [];
        foreach ($files as $file) {
            if (in_array($file->id, $existingSharedFiles)) {
                continue;
            }
            $data[] = [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        Mail::to($user)->send(new ShareFileMail($user, Auth::user(), $files));

        FileShare::insert($data);
        return redirect()->back();

    }

    public function sharedWithMe(Request $request)
    {
        $files = File::getSharedWithMe()->paginate(10);

        $files = FileResource::collection($files);
        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedWithMe', compact('files'));
    }

    public function sharedByMe(Request $request)
    {
        $files = File::getSharedByMe()->paginate(10);

        $files = FileResource::collection($files);
        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedByMe', compact('files'));
    }

    public function downloadSharedWithMeFiles(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }
        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select a file to download' 
            ];
        }
        $zipname = 'shared_with_me';
        if ($all) {
            $files = File::getSharedWithMe()->get();
            $url = $this->createZip($files);
            $file_name = $zipname. '.zip';
        }else {
            [$url, $file_name] = $this->getDownloadUrl($ids, $zipname);
        }

        return [ 
            'url' => $url,
            'file_name' => $file_name
        ];
    }

    public function downloadSharedByMeFiles(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }
        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select a file to download' 
            ];
        }
        $zipname = 'shared_by_me';
        if ($all) {
            $files = File::getSharedByMe()->get();
            $url = $this->createZip($files);
            $file_name = $zipname. '.zip';
        }else {
            [$url, $file_name] = $this->getDownloadUrl($ids, $zipname);
        }

        return [ 
            'url' => $url,
            'file_name' => $file_name
        ];
    }

    private function getDownloadUrl(array $ids, $zipname)
    {
        if (count($ids) === 1) {
            $file = File::find($ids[0]);
            if ($file->is_folder) {
                if ($file->children->count() == 0) {
                    return [
                        'message' => 'There is no files in the folder'
                    ];
                }
                $url = $this->createZip($file->children);
                $file_name = $file->name . '.zip';
            }else {
                $dest = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME);
                Storage::copy($file->storage_path, $dest);

                $url = asset(Storage::url($dest));
                $file_name = $file->name;
            }
        } else {
            $files = File::query()->whereIn('id', $ids)->get();
            $url = $this->createZip($files);
            $file_name = $zipname . '.zip';
        }

        return [$url, $file_name];
    }
    private function addFilesToZip($zip, $files, $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors. $file->name . '/');
            } else {
                $zip->addFile(Storage::path($file->storage_path),  $ancestors. $file->name );
            }
        }
    }
    private function saveFile($file, $parent, $user): void
    {
        /** 
         *  @var \Illuminate\Http\UploadedFile $file 
         * 
        */
        $storage_path = $file->store('/files/'.$user->id);
        $model = new File();
        $model->storage_path = $storage_path;
        $model->is_folder = false; 
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $parent->appendNode($model);
    }
}
