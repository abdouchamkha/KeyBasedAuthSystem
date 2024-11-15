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

class Index extends Controller
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
                // $request = $this->common->decryptJson(json_encode($request->json()->all()));
                $request = $request->json()->all();
            } catch (Exception $th) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid payload.',
                ];
                $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable namee
                return response($responseEnc, 200);
            }
            $response = Http::post($this->webhookUrl, [
                'content' => "incoming from requet from no ui loader Req\n```json\n" . json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '```',
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
            return $this->common->catchTheError('Unknown Error.', 'Unknown', $th->getMessage());
        }
    }
    public function init($request, $ipAddress)
    {
        try {
            if (!$this->common->isValidMd5($request['noui_hash']) or !$this->common->isValidMd5($request['ui_hash'])) {
                return 'ui or noui_hash format is not valid.';
                return $this->common->returnBadRequest('ui or noui_hash format is not valid.');
            }
            // check generated UI loader session
            if (!$this->common->isValidUuid($request['token'])) {
                return 'token is not valid.';
                return $this->common->returnBadRequest('token is not valid.');
            }
            if ($request['token']) {
                $session = LicenseSession::where('token', $request['token'])
                    ->where('type', 'to no-ui')
                    ->first();
                if (!$session) {
                    $response = [
                        'success' => false,
                        'message' => 'Ui loader handshake fails.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                    return response($responseEnc, 200);
                } else {
                    $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
                    if ($sessionTimeout->isPast()) {
                        $response = [
                            'success' => false,
                            'message' => 'Expired token, please try again.',
                        ];
                        return $response;
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 408);
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Token not provided.',
                ];
                return $response;
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 400);
            }
            // check app_id combination
            $app_id = $request['app_id'];
            if (!$this->common->isValidUuid($request['app_id'])) {
                return 'app_id format is not valid.';
                return $this->common->returnBadRequest('app_id format is not valid.');
            } else {
                //check if the app is active
                $application = Application::where('token', $app_id)
                    ->where('status', ActiveType::ACTIVE)
                    ->first();
                if (!$application) {
                    $response = [
                        'success' => false,
                        'message' => 'Application not active or invalid.',
                    ];
                    return $response;
                    Http::post($this->webhookUrl, [
                        'content' => 'Application not active or invalid app token: ' . $request['app_id'] . "\nReq encrypted app token : " . encrypt($request['app_id']),
                    ]);
                    return response($this->common->encryptJson($response), 404);
                }
            }
            // check ui loader hash
            $ui_loader = AuthLoader::select(['id', 'version', 'created_at', 'unsupported_at', 'lang', 'hash'])
                // ->where('app_id', $application->id)
                ->where('hash', $request['ui_hash'])
                ->where('loader_type', 'ui')
                ->orderByDesc('version')
                ->latest()
                ->first();
            if (!$ui_loader) {
                $response = [
                    'success' => false,
                    'message' => 'UI loader not found.',
                ];
                return $response;
                Http::post($this->webhookUrl, [
                    'content' => 'UI loader not found. App token: ' . $request['app_id'] . "\nReq encrypted app token : " . encrypt($request['app_id']),
                ]);
                return response($this->common->encryptJson($response), 404);
            } elseif (!$ui_loader->hash or $ui_loader->hash !== $request['ui_hash']) {
                $response = [
                    'success' => false,
                    'message' => 'UI loader hash is invalid.',
                    'server_hash' => $ui_loader->hash,
                    'req_hash' => $request['ui_hash'],
                ];
                return $response;
                Http::post($this->webhookUrl, [
                    'content' => 'UI loader hash is invalid. HashServer: ' . $ui_loader->hash . '!==' . $request['ui_hash'] . " \n  App token: " . $request['app_id'] . "\nReq encrypted app token : " . encrypt($request['app_id']),
                ]);
                return response($this->common->encryptJson($response), 400);
            }
            //check auth loader
            $auth_loader = AuthLoader::select(['id', 'version', 'created_at', 'unsupported_at', 'lang', 'hash'])
                ->orderByDesc('version')
                ->latest()
                ->limit(2)
                ->get();
            try {
                $usedVersion = null;
                $latestVersion = $auth_loader->first();
                $previousVersion = $auth_loader->last();
                // Determine which version is being used
                if ($request['backend_version'] != $latestVersion->version) {
                    if ($request['backend_version'] == $previousVersion->version && $previousVersion->unsupported_at && $previousVersion->unsupported_at->isPast()) {
                        $response = [
                            'success' => false,
                            'message' => 'Version is outdated.',
                        ];
                        return $response;
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 400);
                    }
                    $usedVersion = $previousVersion;
                } else {
                    $usedVersion = $latestVersion;
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Invalid version. '.$e->getMessage()];
                return $response;
                return response($this->common->encryptJson($response), 200);
            }
            // Check hash using the determined version
            if ($usedVersion && $usedVersion->hash !== $request['noui_hash']) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid hash.',
                ];
                return $response;
                Http::post($this->webhookUrl, [
                    'content' => 'Auth loader Hash not the same. Server hash: ' . $usedVersion->hash . "\nReq hash: " . $request['hash'],
                ]);
                return response($this->common->encryptJson($response), 200);
            }
            // Save session information
            $session->type = 'init';
            $session->duration = 600;
            $session->ip = $ipAddress;
            $session->save();

            $responseBack = [
                'success' => true,
                'message' => 'Initialized',
                'token' => $session->token,
                'newSession' => true,
                'nonce' => Str::random(32),
            ];
            return $responseBack;
            //    Http::post($this->webhookUrl, [
            //         'content' => "res 346\n```json\n".json_encode($responseBack, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."```",
            //     ]);
            // Convert response data to JSON
            $respnseEnc = $this->common->encryptJson($responseBack);
            return response($respnseEnc, 200);
        } catch (Exception $e) {
            return 'Init error '.$e->getMessage();
            return $this->common->catchTheError('Init Error.', 'Init', $e->getMessage());
        }
    }
    public function license($request, $ipAddress)
    {
        try {
            if (!$this->common->isValidUuid($request['token'])) {
                return 'token not provided';
                return $this->common->returnBadRequest();
            }
            if ($request['token']) {
                $session = LicenseSession::where('token', $request['token'])
                    ->where('type', 'init')
                    ->first();
                if (!$session) {
                    $response = [
                        'success' => false,
                        'message' => 'Invalid token.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                    return response($responseEnc, 400);
                } else {
                    $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
                    if ($sessionTimeout->isPast()) {
                        $response = [
                            'success' => false,
                            'message' => 'Expired token, please try again.',
                        ];
                        return $response;
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 408);
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Token not provided.',
                ];
                return $response;
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 400);
            }
            if ($request['license']) {
                $licenseCullection = License::with('hwid')->where('uuid_value', $request['license'])
                    ->orWhere('license_value', $request['license'])
                    ->first();
                if (!$licenseCullection) {
                    $response = [
                        'success' => false,
                        'message' => 'License not found.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 200);
                } elseif ($licenseCullection->banned_at) {
                    $response = [
                        'success' => false,
                        'message' => 'License banned at' . $licenseCullection->banned_at . ' .',
                        'banned_at' => $licenseCullection->banned_at,
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 200);
                } else {
                    if ($licenseCullection->frozen_at) {
                        if ($licenseCullection->freeze_type == 'admin') {
                            $response = [
                                'success' => false,
                                'message' => 'The license is frozen by admin, ask for unfreeze.',
                                'frozen_at' => $licenseCullection->frozen_at,
                            ];
                            return $response;
                            $responseEnc = $this->common->encryptJson($response);
                            return response($responseEnc, 200);
                        } elseif ($licenseCullection->freeze_type == 'timer') {
                            $response = [
                                'success' => false,
                                'message' => 'The license is frozen by timer will automatically unfreeze at ' . $licenseCullection->unfreeze_at . '.',
                                'frozen_at' => $licenseCullection->frozen_at,
                                'unfreeze_at' => $licenseCullection->unfreeze_at,
                            ];
                            return $response;
                            $responseEnc = $this->common->encryptJson($response);
                            return response($responseEnc, 200);
                        } elseif ($licenseCullection->freeze_type == 'defualt') {
                            $licenseCullection->frozen_at = null;
                            $licenseCullection->freeze_type = null;
                            $licenseCullection->save();
                        }
                    }
                    $bannedHwid = LicenseHwid::where('hwid', $request['hwid'])
                    ->orWhere('ip', $ipAddress)
                    ->whereNotNull('banned_at')
                    ->first();
                    if ($bannedHwid) {
                        $response = [
                            'success' => false,
                            'message' => 'The user is banned.',
                            'banned_at' => $bannedHwid->banned_at,
                            'ban_type' => $bannedHwid->ban_type,
                        ];
                        return $response;
                        $responseEnc = $this->common->encryptJson($response);
                        return response($responseEnc, 200);
                    }
                    if (!$licenseCullection->hwid) {
                        // ['license_id','uuid_value', 'ip','hwid','banned_at','ban_type','last_active']
                        $createHwid = new LicenseHwid();
                        $createHwid->license_id = $licenseCullection->id;
                        $createHwid->app_id = $licenseCullection->app_id;
                        $createHwid->product_id = $licenseCullection->product_id;
                        $createHwid->uuid_value = $licenseCullection->uuid_value;
                        $createHwid->ip = $ipAddress;
                        $createHwid->hwid = $request['hwid'];
                        $createHwid->last_active = now();
                        $createHwid->save();
                    } else {
                        // can add IP check as well
                        if ($licenseCullection->hwid->hwid == null || $licenseCullection->hwid->hwid == $request['hwid']) {
                            $licenseCullection->hwid->hwid ??= $request['hwid'];
                            $licenseCullection->hwid->ip = $ipAddress;
                            $licenseCullection->hwid->last_active = now();
                            $licenseCullection->hwid->save();
                        } else {
                            $response = [
                                'success' => false,
                                'message' => 'HWID mismatch.',
                            ];
                            return $response;
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
            } else {
                $response = [
                    'success' => false,
                    'message' => 'License not provided.',
                ];
                return $response;
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 400);
            }
            // Save session information
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
                'token' => $session->token,
                'nonce' => Str::random(32),
            ];
            return $responseBack;
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
            $respnseEnc = $this->common->encryptJson($responseBack);
            return response($respnseEnc, 200);
        } catch (Exception $e) {
            return 'License error  ' . $e->getMessage();
            return $this->common->catchTheError($error = 'License Error.', $errorDiscord = 'License', $e = $e->getMessage());
        }
    }
    public function download($request, $ipAddress = null)
    {
        try {
            if (!$this->common->isValidUuid($request['token'] || !$this->common->isValidUuid($request['license']) || !is_numeric($request['download_id']))) {
                return 'token or license or download_id not provided';
                return $this->common->returnBadRequest();
            }
            if ($request['token']) {
                $session = LicenseSession::where('token', $request['token'])
                    ->whereIn('type', ['license'])
                    ->first();
                if (!$session) {
                    $response = [
                        'success' => false,
                        'message' => 'Invalid token.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response); // Corrected typo in variable name
                    return response($responseEnc, 200);
                } else {
                    $sessionTimeout = Carbon::parse($session->created_at)->addSeconds($session->duration); // Corrected typo
                    if ($sessionTimeout->isPast()) {
                        $response = [
                            'success' => false,
                            'message' => 'Expired token, please try again.',
                        ];
                        return $response;
                        $responseEnc = $this->common->encryptJson($response); // Corrected typo
                        return response($responseEnc, 200);
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Token not provided.',
                ];
                return $response;
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 400);
            }
            if (!empty($request['download_id']) && !empty($request['license'])) {
                $productDownload = ProductDownload::where('id', $request['download_id'])
                    ->with('license')
                    ->first();
                if (!$productDownload) {
                    $response = [
                        'success' => false,
                        'message' => 'The Product download not found.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 404);
                } elseif (!$productDownload->license->id or $productDownload->license->id != $request['license']) {
                    $response = [
                        'success' => false,
                        'message' => 'The license not found or not linked to the specified download.',
                    ];
                    return $response;
                    $responseEnc = $this->common->encryptJson($response);
                    return response($responseEnc, 404);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Download id or license not provided.',
                ];
                return $response;
                $responseEnc = $this->common->encryptJson($response); // Corrected typo
                return response($responseEnc, 400);
            }
            // Check if the file exists
            if (Storage::disk('products_download_disk')->exists($productDownload->path)) {
                // Retrieve the encrypted file content from storage
                $encryptedContent = Storage::disk('products_download_disk')->get($productDownload->path);
                $decryptedContent = decrypt($encryptedContent);
                $contents = bin2hex($decryptedContent);
                // Save session information
                $session->type = 'download';
                $session->duration = 5;
                $session->ip = $ipAddress;
                $session->save();
                $responseBack = [
                    'success' => true,
                    'token' => $session->token,
                    'message' => 'File download successful',
                    'contents' => "$contents",
                    'nonce' => Str::random(32),
                ];
            } else {
                // File doesn't exist
                $responseBack = [
                    'success' => false,
                    'message' => 'File not found',
                ];
                return $responseBack;
                $responseEnc = $this->common->encryptJson($responseBack);
                return response($responseEnc, 404);
            }
            return $responseBack;
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
            return 'Download error '.$e->getMessage();
            return $this->common->catchTheError($error = 'Download Error.', $errorDiscord = 'Download', $e = $e->getMessage());
        }
    }
}
