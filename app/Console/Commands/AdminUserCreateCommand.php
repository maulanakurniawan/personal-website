<?php
namespace App\Console\Commands;
use App\Models\AdminUser;
use Illuminate\Console\Command;
class AdminUserCreateCommand extends Command { protected $signature='admin-user:create {name} {email} {--password=}'; protected $description='Create an Admin Hub user'; public function handle(){ if(AdminUser::where('email',$this->argument('email'))->exists()){ $this->error('Admin user already exists.'); return self::FAILURE;} $p=$this->option('password') ?: $this->secret('Password'); if(!$this->option('password') && $p!==$this->secret('Confirm password')){ $this->error('Password confirmation does not match.'); return self::FAILURE;} $u=AdminUser::create(['name'=>$this->argument('name'),'email'=>$this->argument('email'),'password'=>$p,'is_active'=>true]); $this->info("Admin user created successfully.\n"); $this->line('Name: '.$u->name); $this->line('Email: '.$u->email); $this->line('Active: yes'); return self::SUCCESS; }}
