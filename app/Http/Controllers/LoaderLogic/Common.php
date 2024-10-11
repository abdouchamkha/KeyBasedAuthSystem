<?php

namespace App\Http\Controllers\LoaderLogic;

use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class Common extends Controller
{
    private $webhookUrl = 'https://discord.com/api/webhooks/1181722302187053106/irZOETmOGptFxB-BfSU501o09be2yuoMT45EHs0Ym86mkSj51SBHdi6ucQsXLIr0BWkL';
    private $encrptionkey = '4b4f26b6-37c6-4e9c-aa9e-aa08e4f173fe';
    /**
     * get the errors and return them as decrtpyed response
     * @param mixed $error
     * @param mixed $errorDiscord
     * @param mixed $e
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function catchTheError($error = null, $errorDiscord = null, $e)
    {
        $response = [
            'success' => false,
            'message' => $error ?? 'Unknow error',
        ];
        // $responseBackJson = json_encode($response);
        $respnseEnc = $this->encryptJson($response);
        // $sig = hash_hmac('sha256', $responseBackJson, $this->secret);
        // header("signature: {$sig}");
        if ($errorDiscord) {
            $response = Http::post($this->webhookUrl, [
                'content' => $errorDiscord . ' Error.' . $e,
            ]);
        }
        return response($respnseEnc, 500);
    }
    // Custom base64 encoding function
    public function base64encode($input)
    {
        return str_replace('=', '', base64_encode($input));
    }
    // Custom base64 decoding function
    public function base64decode($input)
    {
        $padding = strlen($input) % 4;
        if ($padding) {
            $input .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($input);
    }
    /* Custom encryption function using XOR with a secret key */
    function encrypt($data, $key)
    {
        // Convert boolean to string
        if (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        }
        $encrypted = '';
        $len = strlen($data);
        $keyLen = strlen($key);
        for ($i = 0; $i < $len; ++$i) {
            $encrypted .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return $encrypted;
    }
    /* Custom decryption function using XOR with a secret key */
    function decrypt($data, $key)
    {
        return $this->encrypt($data, $key); // XOR encryption and decryption are the same operation
    }
    /** This will encrypt the given string */
    public function encryptString(string $data): string{
        return $this->base64encode($this->encrypt($data, $this->encrptionkey));
    }
    /** This will decrypt the given string */
    public function decryptString(string $data): string{
        return $this->decrypt($this->base64decode($data), $this->encrptionkey);
    }
    /** This will encrypt the given string json */
    public function encryptJson(array $data)
    {
        $encrypted = $data;
        // Encrypt the keys and values separately
        $encryptedKeys = [];
        $encryptedValues = [];
        foreach ($encrypted as $key => $value) {
            $encryptedKeys[$key] = $this->base64encode($this->encrypt($key, $this->encrptionkey));
            $encryptedValues[$key] = $this->base64encode($this->encrypt($value, $this->encrptionkey));
        }

        // Construct encrypted JSON string
        $encryptedJson = '{';
        foreach ($encrypted as $key => $value) {
            $encryptedJson .= '"' . $encryptedKeys[$key] . '":"' . $encryptedValues[$key] . '",';
        }
        $encryptedJson = rtrim($encryptedJson, ',') . '}';
        return $encryptedJson;
    }
     /** This will decrypt the given string json */
    public function decryptJson($data)
    {
        $encryptedJson = $data;
        // Decrypt the JSON string
        $decodedData = json_decode($encryptedJson, true);
        $decryptedArray = [];
        foreach ($decodedData as $key => $value) {
            $decryptedKey = $this->decrypt($this->base64decode($key), $this->encrptionkey);
            $decryptedValue = $this->decrypt($this->base64decode($value), $this->encrptionkey);
            $decryptedArray[$decryptedKey] = $decryptedValue;
        }
        return $decryptedArray;
    }
    /** Return decrypted bad request response */
    public function returnBadRequest(string $message='Bad request')
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        $respnseEnc = $this->encryptJson($response);
        return response($respnseEnc, 400);
    }
    /** Check if the given string is valid uuid */
    public function isValidUuid($userKey)
    {
        try {
            Uuid::fromString($userKey);
            // If no exception is thrown, $userKey is a valid UUID
            return true;
        } catch (\Throwable $e) {
            // Exception is thrown if $userKey is not a valid UUID
            return false;
        }
    }
    /** Decombaine the generated_uuid from the app_id and check format */
    public function isValidAppId($app_id)
    {
        // A combined UUID string should be exactly 72 characters long (36 + 36)
        if (strlen($app_id) !== 72) {
            return false;
        }

        // Split the combined UUID into two parts
        $generated_uuid = substr($app_id, 0, 36);
        $real_app_id = substr($app_id, 36, 36);

        // Check if both parts are valid UUIDs
        if ($this->isValidUuid($generated_uuid) && $this->isValidUuid($real_app_id)) {
            // Return the real app ID (second part) if both are valid
            return $real_app_id;
        }

        return false;
    }
    /**
     * Check if the given string is MD5 hash format
     */
    public function isValidMd5($string)
    {
        return preg_match('/^[a-f0-9]{32}$/', $string);
    }
}
