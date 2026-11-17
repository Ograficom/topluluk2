<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GoogleDriveConnection extends Model
{
    protected $fillable = ['account_email','access_token','refresh_token','token_expires_at','root_folder_id','scopes','connected_at','last_checked_at','last_error'];
    protected function casts(): array
    {
        return ['access_token'=>'encrypted:array','refresh_token'=>'encrypted','scopes'=>'array','token_expires_at'=>'datetime','connected_at'=>'datetime','last_checked_at'=>'datetime'];
    }
    public static function current(): ?self { return static::query()->first(); }
    public function isConnected(): bool { return filled($this->refresh_token) && filled($this->root_folder_id); }
}