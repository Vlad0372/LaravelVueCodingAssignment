<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ServerStartSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:start-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->populateDB();
    }

    public function populateDB()
    {
        if(!Schema::hasTable('zoho_crm_data')){
            Schema::create('zoho_crm_data', function (Blueprint $table) {
                $table->id();
                $table->text('access_token');
                $table->text('refresh_token');
                $table->text('client_id');
                $table->text('client_secret');
                $table->timestamps();
            });
        }else{
            DB::table('zoho_crm_data')->delete();
        }

        DB::table('zoho_crm_data')->insert([
            'access_token' => Crypt::encryptString(config('zoho_crm_data.access_token', 'undefined')),
            'refresh_token' => Crypt::encryptString(config('zoho_crm_data.refresh_token', 'undefined')),
            'client_id' => Crypt::encryptString(config('zoho_crm_data.client_id', 'undefined')),
            'client_secret' => Crypt::encryptString(config('zoho_crm_data.client_secret', 'undefined'))
        ]);   
        
        if(!Schema::hasTable('deal_stages')){
            Schema::create('deal_stages', function (Blueprint $table) {
                $table->id();
                $table->string('stage_name');
                $table->timestamps();
            });
        }else{
            DB::table('deal_stages')->delete();
        }

        $data = [
            ['stage_name'=>'Qualification'],
            ['stage_name'=>'Needs Analysis'],
            ['stage_name'=>'Value Proposition'],
            ['stage_name'=>'Identify Decision Makers'],
            ['stage_name'=>'Proposal/Price Quote'],
            ['stage_name'=>'Negotiation/Review'],
            ['stage_name'=>'Closed Won'],
            ['stage_name'=>'Closed Lost'],
            ['stage_name'=>'Closed-Lost to Competition'],
        ];

        DB::table('deal_stages')->insert($data);
    
    }
}
