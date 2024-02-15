<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasCreatorAndUpdator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory,HasCreatorAndUpdator, NodeTrait, SoftDeletes;

    public function isOwnedBy($user_id): bool
    {
        return $this->created_by = $user_id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function starred()
    {
        return $this->hasOne(StarredFile::class, 'file_id', 'id')->where('user_id', Auth::id());
    }

    public function isRoot(){
        return $this->parent_id === null;
    }

    public function owner(): Attribute
    {
        return Attribute::make(
            get: function(mixed $value, array $attributes){
                return $attributes['created_by'] == Auth::id() ? 'You' : $this->user->name;
            }
        );

    }

    public function get_file_size()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = ($this->size > 0) ? floor(log($this->size, 1024)) : 0;

        return number_format($this->size / pow(1024, $power), 2, '.', ',', ) .' '.$units[$power];
    }

    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            if (!$model->parent) {
                return;
            }
            $model->path = (!$model->parent->isRoot() ? $model->parent->path . '/' : '') . Str::slug($model->name);
        });

        // static::deleted(function(File $model){
        //     if (!$model->is_folder) {
        //         Storage::delete($model->storage_path);
        //     }else {
        //         $children = $model->children();
        //         foreach ($children as $child) {
        //             Storage::delete($child->storage_path);
        //         }
        //     }
        // });
    }

    public function moveToTrash()
    {
        $this->deleted_at = Carbon::now();

        return $this->save();
    }

    public function deleteForever()
    {
        $this->deleteFromStorage([$this]);
        $this->forceDelete();
    }

    public function deleteFromStorage($files)
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->deleteFromStorage($file->children);
            }else {
                Storage::delete($file->storage_path);
            }
        }
    }

    public static function getSharedWithMe()
    {
        return  File::query()->select('files.*')
                             ->join('file_shares', 'file_shares.file_id', 'files.id')
                             ->where('file_shares.user_id', Auth::id())
                             ->orderBy('file_shares.created_at', 'desc')
                             ->orderBy('files.id', 'desc');   
    }

    public static function getSharedByMe()
    {
        return File::query()->select('files.*')
                            ->join('file_shares', 'file_shares.file_id', 'files.id')
                            ->where('files.created_by', Auth::id())
                            ->orderBy('file_shares.created_at', 'desc')
                            ->orderBy('files.id', 'desc');
    }
}
