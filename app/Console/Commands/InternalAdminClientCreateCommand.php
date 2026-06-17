<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class InternalAdminClientCreateCommand extends Command { protected $signature='internal-admin-client:create {name} {--scopes=read} {--allowed-ips=}'; public function handle(){ $secret=Str::password(48); $client=InternalAdminClient::create(['name'=>$this->argument('name'),'client_id'=>'iac_'.Str::random(32),'client_secret_hash'=>Hash::make($secret),'scopes'=>array_filter(explode(',',$this->option('scopes'))),'allowed_ips'=>$this->option('allowed-ips')?array_filter(explode(',',$this->option('allowed-ips'))):null,'is_active'=>true]); $this->info('Internal admin client created successfully.'); $this->line('Client ID: '.$client->client_id); $this->line('Client Secret: '.$secret); $this->warn('Copy this secret now. It will not be shown again.'); return self::SUCCESS; }}
