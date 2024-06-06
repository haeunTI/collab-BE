<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

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

        $accessToken = self::getAccessToken();

        $folderIdResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/drive/v3/files', [
            'q' => "name='$folderName' and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id, name)',
        ]);

        if ($folderIdResponse->successful()) {
            $folders = json_decode($folderIdResponse->body(), true)['files'];
            if (empty($folders)) {
                // Folder does not exist, create it
                $createFolderResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->post('https://www.googleapis.com/drive/v3/files', [
                    'name' => $folderName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                ]);

                if ($createFolderResponse->successful()) {
                    return json_decode($createFolderResponse->body(), true)['id'];
                } else {
                    throw new \Exception("Failed to create folder on Google Drive: " . $createFolderResponse->body());
                }
            } else {
                return $folders[0]['id'];
            }
        } else {
            throw new \Exception("Failed to fetch folder ID from Google Drive: " . $folderIdResponse->body());
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