<?php

namespace App\Http\Controllers\LoaderLogic;

use Exception;
use Carbon\Carbon;
use App\ActiveType;
use App\Models\License;
use App\Models\AuthLoader;
use App\Models\Application;
use App\Models\LicenseHwid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LicenseSession;
use App\Models\ProductDownload;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class index extends Controller
{
    private $webhookUrl = 'https://discord.com/api/webhooks/1181722302187053106/irZOETmOGptFxB-BfSU501o09be2yuoMT45EHs0Ym86mkSj51SBHdi6ucQsXLIr0BWkL';
    private $common;

    // Initialize the object in the constructor
    public function __construct()
    {
        $this->common = new Common();
    }

    public function index(Request $request)
    {
        try {
            $ipAddress = $request->ip();
            try {
                $request = $this->common->decryptJson(json_encode($request->json()->all()));
            } catch (Exception $th) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid payload.',
                ];
                $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                return response($responseEnc, 200);
            }
            $response = Http::post($this->webhookUrl, [
                'content' => "Req\n```json\n" . json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "```",
            ]);
            if ($request['type'] == 'init') {
                return $this->init($request, $ipAddress);
            } elseif ($request['type'] == 'license') {
                return $this->license($request, $ipAddress);
            } elseif ($request['type'] == 'download') {
                // return $this->download($request);
            } elseif ($request['type'] == 'error_log') {
                // return $this->errorLog($request);
            } elseif ($request['type'] == 'check') {
                // return $this->check();
            } else {
                throw new Exception('Invalid request type');
            }
        } catch (Exception $th) {
            return $this->common->catchTheError('Unknown Error.', 'Unknown',  $th->getMessage());
        }
    }
    public function init($request, $ipAddress)
    {
        try {
            $auth_loader = AuthLoader::select(['id', 'version', 'created_at', 'unsupported_at', 'hash'])
                ->latest()
                ->limit(2)
                ->get();
            try {
                $usedVersion = null;
                $latestVersion = $auth_loader->first();
                $previousVersion = $auth_loader->last();
                // Determine which version is being used
                if ($request['ver'] != $latestVersion->version) {
                    if ($request['ver'] == $previousVersion->version && $previousVersion->unsupported_at && $previousVersion->unsupported_at->isPast()) {
                        abort(404, 'Version is outdated.');
                    }
                    $usedVersion = $previousVersion;
                } else {
                    $usedVersion = $latestVersion;
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Invalid version.'];
                Http::post($this->webhookUrl, [
                    'content' => "res 303\n```json\n" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\nERROR E:" . $e->getMessage() . "```",
                ]);
                return response($this->common->encryptJson($response), 200);
            }
            if (!$this->common->isValidUuid($request['app_id'])) {
                return $this->common->returnBadRequest();
            }
            //check if the app is active
            if(!Application::where('token',Hash::make($request['app_id']))->where('status',ActiveType::ACTIVE)->exists()){
                $response = [
                    'success' => false,
                    'message' => 'Application not active or invalid.',
                ];
                Http::post($this->webhookUrl, [
                    'content' => "Application not active or invalid app token: " . $request['app_id'] . "\nReq hased token : " . Hash::make($request['app_id']),
                ]);
                return response($this->common->encryptJson($response), 404);
            }
            // Check hash using the determined version
            if ($usedVersion && $usedVersion->hash !== $request['hash']) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid hash.',
                ];
                Http::post($this->webhookUrl, [
                    'content' => "Hash not the same. Server hash: " . $usedVersion->hash . "\nReq hash: " . $request['hash'],
                ]);
                return response($this->common->encryptJson($response), 200);
            }
            // Save session information
            $session = new LicenseSession();
            $session->app_id = $request['app_id'];
            $session->type = 'init';
            $session->duration = 5;
            $session->ip = $ipAddress;
            $session->save();

            $responseBack = [
                'success' => true,
                'message' => 'Initialized',
                'token' => $session->token,
                'newSession' => true,
                'nonce' => Str::random(32),
            ];
            //    Http::post($this->webhookUrl, [
            //         'content' => "res 346\n```json\n".json_encode($responseBack, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."```",
            //     ]);
            // Convert response data to JSON
            $respnseEnc = $this->common->encryptJson($responseBack);
            return response($respnseEnc, 200);
        } catch (Exception $e) {
            return $this->common->catchTheError($error = 'Init Error.', $errorDiscord = 'Init', $e = $e->getMessage());
        }
    }
    public function license($request,$ipAddress)
    {
        try {
            if (!$this->common->isValidUuid($request['token'])) {
                return $this->common->returnBadRequest();
            }
            if($request['token']){
                $session = LicenseSession::where('token', $request['token'])->first();
                if(!$session){
                    $response = [
                        'success' => false,
                        'message' => 'Invalid token.',
                    ];
                    $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                    return response($responseEnc, 200);
                } else {
                    $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
                    if($sessionTimeout->isPast()){
                        $response = [
                            'success' => false,
                            'message' => 'Expired token, please try again.',
                        ];
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 200);
                    }
                }
            } else {
                abort(403, 'Token not provided.');
            }
            if($request['license']){
                $licenseCullection = License::where('license_uuid', $request['license'])->orWhere('license_value',$request['license'])->first();
                if(!$licenseCullection){
                    $response = [
                        'success' => false,
                        'message' => 'License not found.',
                    ];
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 200);
                }elseif($licenseCullection->banned_at){
                    $response = [
                        'success' => false,
                        'message' => 'License banned at'.$licenseCullection->banned_at.' .',
                        'banned_at' => $licenseCullection->banned_at
                    ];
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 200);
                }else{
                    if($licenseCullection->frozen_at){
                        if($licenseCullection->freeze_type == 'admin'){
                            $response = [
                               'success' => false,
                               'message' => 'The license is frozen by admin, ask for unfreeze.',
                               'frozen_at'=>$licenseCullection->frozen_at
                            ];
                            $responseEnc = $this->common->encryptJson($response);
                            return response($responseEnc, 200);
                        }elseif($licenseCullection->freeze_type == 'timer'){
                            $response = [
                                'success' => false,
                                'message' => 'The license is frozen by timer will automatically unfreeze at '.$licenseCullection->unfreeze_at.'.',
                                'frozen_at'=>$licenseCullection->frozen_at,
                                'unfreeze_at'=>$licenseCullection->unfreeze_at
                             ];
                             $responseEnc = $this->common->encryptJson($response);
                             return response($responseEnc, 200);
                        }elseif($licenseCullection->freeze_type =='defualt'){
                            $licenseCullection->frozen_at = null;
                            $licenseCullection->freeze_type = null;
                            $licenseCullection->save();
                        }
                    }
                    $bannedHwid = LicenseHwid::where('hwid',$request['hwid'])
                    ->orWhere('ip',$ipAddress)->whereNotNull('banned_at')->first();
                    if($bannedHwid){
                        $response = [
                           'success' => false,
                           'message' => 'The user is banned.',
                           'banned_at'=>$bannedHwid->banned_at,
                           'ban_type'=> $bannedHwid->ban_type
                        ];
                        $responseEnc = $this->common->encryptJson($response);
                        return response($responseEnc, 200);
                    }
                    $hwid = LicenseHwid::where('license_id', $licenseCullection->id)->latest()->first();
                    if(!$hwid){
                        // ['license_id','uuid_value', 'ip','hwid','banned_at','ban_type','last_active']
                        $createHwid = new LicenseHwid();
                        $createHwid->license_id = $licenseCullection->id;
                        $createHwid->uuid_value = $licenseCullection->uuid_value;
                        $createHwid->ip = $ipAddress;
                        $createHwid->hwid = $request['hwid'];
                        $createHwid->last_active = now();
                        $createHwid->save();
                    }else{
                        // can add IP check as well
                        if(($hwid->hwid == null )||($hwid->hwid == $request['hwid'])){
                            $hwid->hwid ??= $request['hwid'];
                            $hwid->ip = $ipAddress;
                            $hwid->last_active = now();
                            $hwid->save();
                        }else{
                            $response = [
                                'success' => false,
                                'message' => 'HWID mismatch.',
                            ];
                            $responseEnc = $this->common->encryptJson($response);
                            return response($responseEnc, 200);
                        }
                    }
                }
                //         if(($hwid->hwid == null && $hwid->ip_address == null)||($hwid->ip_address == $ipAddress && $hwid->hwid == $request['hwid'])){
                //             $hwid->hwid = $request['hwid'];
                //             $hwid->ip_address = $ipAddress;
                //             $hwid->last_active = now();
                //             $hwid->save();
                //         }else{
                //            // return hwid mismatch
                //         }
                //     }
                // }
            }else{
                abort(403, 'License not provided.');
            }
            // Save session information
            $session = new LicenseSession();
            $session->app_id = $request['app_id'];
            $session->type = 'license';
            $session->duration = 5;
            $session->ip = $ipAddress;
            $session->save();
            $responseBack = [
                'success' => 'true',
                'message' => 'Loggeed in!',
                'data_uuid_value' => $licenseCullection->uuid_value,
                'data_license_value' => $licenseCullection->license_value,
                'data_hwid' => $request['hwid'],
                'nonce' => Str::random(32),
            ];
// try {
//     $requestData = json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
//     $messageContent = "Requst resived : \n```json\n" . $requestData . "\n```";
//     $response = Http::post($this->webhookUrl, [
//         'content' => $messageContent
//     ]);
//     $requestDataback = json_encode($responseBack, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
//     $messageContentback = "Requst back : \n```json\n" . $requestDataback . "\n```";
//     $response = Http::post($this->webhookUrl, [
//         'content' => $messageContentback
//     ]);
// } catch (Exception $e) {
//     $errorMessage = "Error occurred: License" . $e->getMessage();
//     $response = Http::post($this->webhookUrl, [
//         'content' => $errorMessage
//     ]);
// }
            $respnseEnc = $this->common->encryptJson($responseBack);;
            return response($respnseEnc, 200);
        } catch (Exception $e) {
            return $this->common->catchTheError($error = 'License Error.', $errorDiscord = 'License', $e = $e->getMessage());
        }
    }
    public function download($request,$ipAddress=null)
    {
        try {
            if (!$this->common->isValidUuid($request['token']||!$this->common->isValidUuid($request['license'])||!is_numeric($request['download_id']))) {
                return $this->common->returnBadRequest();
            }
            if($request['token']){
                $session = LicenseSession::where('token', $request['token'])->first();
                if(!$session){
                    $response = [
                        'success' => false,
                        'message' => 'Invalid token.',
                    ];
                    $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                    return response($responseEnc, 200);
                } else {
                    $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
                    if($sessionTimeout->isPast()){
                        $response = [
                            'success' => false,
                            'message' => 'Expired token, please try again.',
                        ];
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 200);
                    }
                }
            } else {
                abort(403, 'Token not provided.');
            }
            if(!empty($request['download_id'])&&!empty($request['license'])){
                $productDownload = ProductDownload::where('id', $request['download_id'])->with('license')->first();
                if(!$productDownload){
                    $response = [
                        'success' => false,
                        'message' => 'The Product download not found.',
                    ];
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 404);
                }elseif(!$productDownload->license->id OR $productDownload->license->id != $request['license']){
                    $response = [
                        'success' => false,
                        'message' => 'The license not found or not linked to the specified download.',
                    ];
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 404);
                }
            }else{
                abort(403,'Download id or license not provided.');
            }
            // Check if the file exists
            if (Storage::disk('products_download_disk')->exists($productDownload->path)) {
                // Retrieve the encrypted file content from storage
                $encryptedContent = Storage::disk('products_download_disk')->get($productDownload->path);
                $decryptedContent = decrypt($encryptedContent);
                $contents = bin2hex($decryptedContent);
                $responseBack =  [
                    'success' => true,
                    'message' => 'File download successful',
                    'contents' => "$contents",
                    'nonce' => Str::random(32),
                ];
            } else {
                // File doesn't exist
                $responseBack = [
                    'success' => false,
                    'message' => 'File not found'
                ];
                $responseEnc = $this->common->encryptJson($responseBack);
                return response($responseEnc, 404);
            };
            $respnseEnc = $this->common->encryptJson($responseBack);
            return response($respnseEnc, 200);
        } catch (Exception $e) {
            // try {
            //     $storeError = StoresReseller::where('store_id',$request['ownerid'])->first();
            //     $requestData = json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            //     $messageContent = "Store name : ".$storeError?->store_name."\n".
            //     "Store id : ".$storeError?->id."\n".
            //     "Store discord : ".$storeError?->store_link."\n".
            //     "\n```json\n" . $requestData . "\n```";

            //     $response = Http::post($this->webhookUrl, [
            //         'content' => $messageContent
            //     ]);
            // } catch (Exception $e) {
                // $errorMessage = "Error occurred: " . $e->getMessage();
                // $response = Http::post($this->webhookUrl, [
                //     'content' => $errorMessage
            //     ]);
            // }
            return $this->common->catchTheError($error = 'Download Error.', $errorDiscord = 'Download', $e = $e->getMessage());
        }
    }
}
