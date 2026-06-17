<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
class InternalAdminClientDisableCommand extends Command { protected $signature='internal-admin-client:disable {client_id}'; public function handle(){ InternalAdminClient::where('client_id',$this->argument('client_id'))->firstOrFail()->update(['is_active'=>false]); $this->info('Internal admin client disabled successfully.'); return self::SUCCESS; }}
