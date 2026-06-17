<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class InternalAdminClientRotateCommand extends Command { protected $signature='internal-admin-client:rotate {client_id}'; public function handle(){ $c=InternalAdminClient::where('client_id',$this->argument('client_id'))->firstOrFail(); $secret=Str::password(48); $c->update(['client_secret_hash'=>Hash::make($secret)]); $this->info('Internal admin client secret rotated successfully.'); $this->line('Client ID: '.$c->client_id); $this->line('Client Secret: '.$secret); $this->warn('Copy this secret now. It will not be shown again.'); return self::SUCCESS; }}
