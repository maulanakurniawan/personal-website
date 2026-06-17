<?php
namespace App\Console\Commands;
use App\Models\InternalAdminClient;
use Illuminate\Console\Command;
class InternalAdminClientListCommand extends Command { protected $signature='internal-admin-client:list'; public function handle(){ $this->table(['ID','Name','Client ID','Scopes','Active','Revoked At','Last Used At'], InternalAdminClient::orderBy('id')->get()->map(fn($c)=>[$c->id,$c->name,$c->client_id,implode(',',$c->scopes?:[]),$c->is_active?'yes':'no',$c->revoked_at?->toDateTimeString(),$c->last_used_at?->toDateTimeString()])->all()); return self::SUCCESS; }}
