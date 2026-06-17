<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserEnableCommand extends Command { protected $signature='admin-user:enable {email}'; protected $description='Enable an Admin Hub user'; public function handle(){ AdminUser::where('email',$this->argument('email'))->firstOrFail()->update(['is_active'=>true]); $this->info('Admin user enabled successfully.'); return self::SUCCESS; }}
