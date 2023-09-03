<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ZohoRecordController extends Controller
{
    private $requestFailed = false;
    private $responseMsg = "";
    
    public function create(Request $request)
    { 
        $possibleErrorBag = $this->validateRequest($request);  
        if(is_object($possibleErrorBag)) return $possibleErrorBag;

        $this->regenerateTokenIfNeeded();

        if(!$this->requestFailed)
        {
            try
            {
                $access_data = DB::table('zoho_crm_data')->first();
                $token = Crypt::decryptString($access_data->access_token);

                $accountId = $this->postAccount($request, $token);
                $this->postDeal($request, $token, $accountId);
            }
            catch (DecryptException $e) 
            {
                $this->requestFailed = true;
                $this->responseMsg = "failed to decrypt access data";
            }
        }
 
        return response()->json([
            'request_failed' => $this->requestFailed,
            'response_msg' => $this->responseMsg,
        ]);
    }

    public function postAccount($request, $token)
    {
        $response = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $token])
        ->post('https://www.zohoapis.eu/crm/v2/Accounts', [
            'data' => [
                [
                    'Account_Name' => $request->accountName,
                    'Website' => $request->website,
                    'Phone' => $request->phone,
                ],
            ]    
        ]);

        if($response->created())
        {
            $response = json_decode($response, true);
            $response = $response['data'][0];

            return $response['details']['id'];
        }
        else
        {
            $this->requestFailed = true;
            $this->responseMsg = "Failed to create Account record";

            return 0;
        }
    }
    public function postDeal($request, $token, $accountId)
    {
        $stage = DB::table('deal_stages')->where('id', $request->stage)->value('stage_name');
   
        $response = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $token])
        ->post('https://www.zohoapis.eu/crm/v2/Deals', [
            'data' => [
                [
                    'Deal_Name' => $request->dealName,
                    'Stage' => $stage,
                    'Account_Name' => [
                        'name' => $request->accountName,
                        'id' => $accountId
                    ]
                ]
            ]
        ]);

        if(!$response->created())
        {
            $this->requestFailed = true;
            $this->responseMsg = "Failed to create Deal record";
        }
    }
    public function regenerateTokenIfNeeded()
    {       
        try 
        {
            $access_data = DB::table('zoho_crm_data')->first();

            $token = Crypt::decryptString($access_data->access_token);
            
            $response = Http::withHeaders(['Authorization' => 'Zoho-oauthtoken ' . $token])
            ->get('https://www.zohoapis.eu/crm/v2/Leads/search?word=test_request');

            if($response->failed())
            {
                $response = Http::withUrlParameters([
                    'refresh_token' => Crypt::decryptString($access_data->refresh_token),
                    'client_id' => Crypt::decryptString($access_data->client_id),
                    'client_secret' => Crypt::decryptString($access_data->client_secret),
                ])
                ->post('https://accounts.zoho.eu/oauth/v2/token?refresh_token={refresh_token}&client_id={client_id}&client_secret={client_secret}&grant_type=refresh_token');
                
                $response = json_decode($response, true);

                if(isset($response['access_token']))
                {
                    $token = $response['access_token'];
    
                    DB::table('zoho_crm_data')
                    ->where('id', $access_data->id)
                    ->update(['access_token' => Crypt::encryptString($token)]);
                }
                else
                {
                    $this->requestFailed = true;
                    $this->responseMsg = "failed to refresh access token";
                }
            }
        } 
        catch (DecryptException $e) 
        {
            $this->requestFailed = true;
            $this->responseMsg = "failed to decrypt access data";
        }
    }
    public function getDealStages()
    { 
        $deal_stages = DB::table('deal_stages')->get();

        return json_encode($deal_stages);
    }
    public function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'dealName' => [
                'required',
                'max:255',
                "regex:/^[a-zA-Z0-9 @#$%^&*()_+[\]{}|~\-!']+$/",
            ],  
            'stage' => 'required',
            'accountName' => [
                'required',
                'max:255',
                "regex:/^[a-zA-Z0-9 @#$%^&*()_+[\]{}|~\-!']+$/",
            ],  
            'website' => [
                'required', 
                'regex:/^(http:\/\/www\.|https:\/\/www\.|ftp:\/\/www\.|www\.|http:\/\/|https:\/\/|ftp:\/\/)?[^\x00-\x19\x22-\x27\x2A-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B\x7D-\x7F]+(\.[^\x00-\x19\x22\x24-\x2C\x2E-\x2F\x3C\x3E\x40\x5B-\x5E\x60\x7B\x7D-\x7F]+)+(\/[^\x00-\x19\x22\x3C\x3E\x5E\x7B\x7D-\x7D\x7F]*)*$/',
            ],
            'phone' => [
                'required',
                'max:30',
                'regex:/^([\+]?)(?![\.-])(?>(?>[\.-]?[ ]?[\da-zA-Z]+)+|([ ]?\((?![\.-])(?>[ \.-]?[\da-zA-Z]+)+\)(?![\.])([ -]?[\da-zA-Z]+)?)+)+(?>(?>([,]+)?[;]?[\da-zA-Z]+)+)?[;]?$/',
            ],
        ]);
      
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return true;
    }
}
