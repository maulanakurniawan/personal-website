<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserResetPasswordCommand extends Command { protected $signature='admin-user:reset-password {email} {--password=}'; protected $description='Reset an Admin Hub user password'; public function handle(){ $u=AdminUser::where('email',$this->argument('email'))->firstOrFail(); $p=$this->option('password') ?: $this->secret('Password'); if(!$this->option('password') && $p!==$this->secret('Confirm password')){ $this->error('Password confirmation does not match.'); return self::FAILURE;} $u->update(['password'=>$p]); $this->info('Admin user password reset successfully.'); return self::SUCCESS; }}
