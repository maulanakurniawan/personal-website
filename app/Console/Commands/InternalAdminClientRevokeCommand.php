<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
class InternalAdminClientRevokeCommand extends Command { protected $signature='internal-admin-client:revoke {client_id}'; public function handle(){ InternalAdminClient::where('client_id',$this->argument('client_id'))->firstOrFail()->update(['revoked_at'=>now(),'is_active'=>false]); $this->info('Internal admin client revoked successfully.'); return self::SUCCESS; }}
