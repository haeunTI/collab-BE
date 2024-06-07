<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDriveController 
{
    public static function getAccessToken() {
        $client_id = \Config('services.google.client_id');
        $client_secret = \Config('services.google.client_secret');
        $refresh_token = \Config('services.google.refresh_token');
        $response= Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $accessToken = json_decode((string)$response->getBody(), true)['access_token'];
        return $accessToken;

    }

    public static function checkAndCreateFolder($folderName) {
        try {
            $accessToken = self::getAccessToken();
            $parentFolderId = '1rkJI5WcNeEHiyqbQyV5ujBbbDBoSQwoi';
    
            // Check if the target folder exists inside the parent folder
            $folderIdResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://www.googleapis.com/drive/v3/files', [
                'q' => "name='$folderName' and mimeType='application/vnd.google-apps.folder' and trashed=false and '$parentFolderId' in parents",
                'fields' => 'files(id, name)',
            ]);
    
            if ($folderIdResponse->successful()) {
                $folders = json_decode($folderIdResponse->body(), true)['files'];
                if (empty($folders)) {
                    // Folder does not exist, create it inside the parent folder
                    $createFolderResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                    ])->post('https://www.googleapis.com/drive/v3/files', [
                        'name' => $folderName,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [$parentFolderId],
                    ]);
    
                    if ($createFolderResponse->successful()) {
                        return json_decode($createFolderResponse->body(), true)['id'];
                    } else {
                        Log::error('Failed to create folder on Google Drive', [
                            'response_body' => $createFolderResponse->body(),
                            'response_status' => $createFolderResponse->status(),
                        ]);
                        throw new \Exception("Failed to create folder on Google Drive: " . $createFolderResponse->body());
                    }
                } else {
                    return $folders[0]['id'];
                }
            } else {
                Log::error('Failed to fetch folder ID from Google Drive', [
                    'response_body' => $folderIdResponse->body(),
                    'response_status' => $folderIdResponse->status(),
                ]);
                throw new \Exception("Failed to fetch folder ID from Google Drive: " . $folderIdResponse->body());
            }
        } catch (\Throwable $th) {
            Log::error('Exception in checkAndCreateFolder', ['error' => $th->getMessage()]);
            throw $th;
        }
    }
    
    
    public static function deleteOldImageFromDrive($imageName)
    {
        $accessToken = self::getAccessToken();

        $fileIdResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/drive/v3/files', [
            'q' => "name='$imageName' and trashed=false",
            'fields' => 'files(id, name)',
        ]);

        if ($fileIdResponse->successful()) {
            $files = json_decode($fileIdResponse->body(), true)['files'];
            if (!empty($files)) {
                $fileId = $files[0]['id'];

                $deleteResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->delete("https://www.googleapis.com/drive/v3/files/$fileId");

                if (!$deleteResponse->successful()) {
                    throw new \Exception("Failed to delete image from Google Drive: " . $deleteResponse->body());
                }
            }
        } else {
            throw new \Exception("Failed to fetch file ID from Google Drive: " . $fileIdResponse->body());
        }
    }
}

?>