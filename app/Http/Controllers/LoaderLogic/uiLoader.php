<?php

namespace App\Http\Controllers\LoaderLogic;

use Exception;
use Carbon\Carbon;
use App\ActiveType;
use App\Models\License;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LicenseSession;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\License as ResourceLicense;

class uiLoader extends Controller
{
    private $webhookUrl = 'https://discord.com/api/webhooks/1181722302187053106/irZOETmOGptFxB-BfSU501o09be2yuoMT45EHs0Ym86mkSj51SBHdi6ucQsXLIr0BWkL';
    private $common;

    // Initialize the object in the constructor
    public function __construct()
    {
        $this->common = new Common();
    }
    public function getUiLoaderVersion(Request $request)
    {
        // check app_id from the headers and get the version of the loader in the app
        if(!$request['version']OR!is_double( $request['version'])){
            return $this->common->returnBadRequest('The version parameter need to be decimal.');
        }
    }
    public function init(Request $request)
    {
        Http::post($this->webhookUrl, [
            'content' => "Init Dump Body:\n ```" . json_encode($request->all()) . "```\nHeaders : \n```" . json_encode($request->headers->all())."```",
        ]);
        // if (!$this->common->isValidMd5($request['ui_hash'])) {
        //     return $this->common->returnBadRequest('ui hash format is invalid.');
        // }
        // verify app_token header and get the license informationl
        if(!$request->header('appid')){
            return $this->common->returnBadRequest('the app_id is required');
        }
        try {
            $app_id = $this->common->decryptString($request->header('appid'));
        } catch (Exception $e) {
            return $this->common->catchTheError('invalid_payload.', 'Faild to decrypt the app_id in the UI loader key fetch ',  $e->getMessage());
        }
        //check if the app is active
        $application= Application::where('token',($app_id))->where('status',ActiveType::ACTIVE)->first();
        if(!$application){
            $response = [
                'success' => false,
                'message' => 'Application not active or invalid.',
            ];
            Http::post($this->webhookUrl, [
                'content' => "Application not active or invalid app token: " . $request['app_id'] . "\nReq encrypted app token : " . encrypt($request['app_id']),
            ]);
            return response($this->common->encryptJson($response), 404);
        }
        //create session
        $session = new LicenseSession();
        $session->app_id = $application->id;
        $session->type = 'ui init';
        $session->duration = 600;
        $session->ip = $request->ip();
        $session->save();

        $response = [
            'success' => true,
            'token' => $session->token,
        ];
        // Http::post($this->webhookUrl, [
        //     'content' => "```New ui loader session created app token: " . $request['app_id'] . "\nSession token : " . $session->token."```",
        // ]);
        return response($this->common->encryptJson($response), 200);
    }
    /**
     * get license fro the ui loader
     * @param mixed $license
     */
    public function getLicense(string $license,Request $request)
    {
        Http::post($this->webhookUrl, [
            'content' => "Init Dump Body:\n ```" . json_encode($request->all()) . "```\nHeaders : \n```" . json_encode($request->headers->all())."```",
        ]);
        // check generated UI loader session
        $token = $request->header($this->common->decryptString('token'));
        if (!$token OR !$this->common->isValidUuid($this->common->decryptString($token))) {
            return $this->common->returnBadRequest('Token format is not valid.');
        }
       $token = $this->common->decryptString($token);
        $session = LicenseSession::where('token', $token)->where('type','ui init')->first();
        if(!$session){
            $response = [
                'success' => false,
                'message' => 'Ui loader handshake fails.',
            ];
            $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable namee
            return response($responseEnc, 400);
        } else {
            $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
            if($sessionTimeout->isPast()){
                $response = [
                    'success' => false,
                    'message' => 'Request timeout, please try again.',
                ];
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 408);
            }
        }
        // verify app_id header and get the license informationl
        if(!$request->header('appid')){
            return $this->common->returnBadRequest('the app_id is required');
        }
        try {
            $app_id = $this->common->decryptString($request->header('appid'));
        } catch (Exception $e) {
            return $this->common->catchTheError('invalid_payload.', 'Faild to decrypt the app_id in the UI loader key fetch ',  $e->getMessage());
        }
        //check if the app is active
        $application= Application::where('token',($app_id))->where('status',ActiveType::ACTIVE)->first();
        if(!$application){
            $response = [
                'success' => false,
                'message' => 'Application not active or invalid.',
            ];
            Http::post($this->webhookUrl, [
                'content' => "Application not active or invalid app token: " . $request['app_id'] . "\nReq encrypted app token : " . encrypt($request['app_id']),
            ]);
            return response($this->common->encryptJson($response), 404);
        }
        // get the license information from the database or API
        $license = License::where('license_value', $license)->orWhere('uuid_value',$license)->first();

        // If the license is not found, return an error
        if (!$license || !$license->app_id) {
            $response = [
                'success' => false,
                'message' => 'Invaild license.',
            ];
            return response($this->common->encryptJson($response), 404);
            }
            // Ensure the license belongs to the authenticated user
        if ($license->app_id !== $application->id OR $application->id !== $session->app_id) {
            $response = [
                'success' => false,
                'message' => 'Unauthorized access to this license.',
            ];
            return response($this->common->encryptJson($response), 403);
        }
        try {
            // Save session information
            $session->app_id = $application->id;
            $session->type = 'to no-ui';
            $session->duration = 600;
            $session->ip = $request->ip();
            $session->save();
            header($this->common->encryptString('token').":".$this->common->encryptString($session->token));
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Unknown error occurred.',
            ];
            return response($this->common->encryptJson($response), 500);
        }
        // Return the resource
        return new ResourceLicense($license);
    }
}
