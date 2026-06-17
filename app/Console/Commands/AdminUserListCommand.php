<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserListCommand extends Command { protected $signature='admin-user:list'; protected $description='List Admin Hub users'; public function handle(){ $this->table(['ID','Name','Email','Active','Last Login At','Created At'], AdminUser::query()->orderBy('id')->get()->map(fn($u)=>[$u->id,$u->name,$u->email,$u->is_active?'yes':'no',optional($u->last_login_at)->toDateTimeString(),$u->created_at?->toDateTimeString()])->all()); return self::SUCCESS; }}
