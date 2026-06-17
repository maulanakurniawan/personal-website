<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserDisableCommand extends Command { protected $signature='admin-user:disable {email}'; protected $description='Disable an Admin Hub user'; public function handle(){ AdminUser::where('email',$this->argument('email'))->firstOrFail()->update(['is_active'=>false]); $this->info('Admin user disabled successfully.'); return self::SUCCESS; }}
