<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserDeleteCommand extends Command { protected $signature='admin-user:delete {email}'; protected $description='Delete an Admin Hub user'; public function handle(){ $u=AdminUser::where('email',$this->argument('email'))->firstOrFail(); if(!$this->confirm('Delete this admin user?')) return self::FAILURE; $u->delete(); $this->info('Admin user deleted successfully.'); return self::SUCCESS; }}
