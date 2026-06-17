<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
class InternalAdminClientEnableCommand extends Command { protected $signature='internal-admin-client:enable {client_id}'; public function handle(){ InternalAdminClient::where('client_id',$this->argument('client_id'))->firstOrFail()->update(['is_active'=>true,'revoked_at'=>null]); $this->info('Internal admin client enabled successfully.'); return self::SUCCESS; }}
