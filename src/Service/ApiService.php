<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class ApiService
{
    private HttpClientInterface $client;
    private const API_BASE = 'https://127.0.0.1:3000/api';

    public function __construct(HttpClientInterface $apiClient)
    {
        $this->client = $apiClient;
    }

    /**
     * Effectue une requête GET vers l'API
     */
    public function get(string $endpoint, ?string $token = null): array
    {
        $headers = [];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = $this->client->request('GET', self::API_BASE . $endpoint, [
            'headers' => $headers
        ]);

        return $response->toArray();
    }

    /**
     * Effectue une requête POST vers l'API
     */
    public function post(string $endpoint, array $data = [], ?string $token = null): array
    {
        $headers = [];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = $this->client->request('POST', self::API_BASE . $endpoint, [
            'headers' => $headers,
            'json' => $data
        ]);

        return $response->toArray();
    }

    /**
     * Effectue une requête PUT vers l'API
     */
    public function put(string $endpoint, array $data = [], ?string $token = null): array
    {
        $headers = [];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = $this->client->request('PUT', self::API_BASE . $endpoint, [
            'headers' => $headers,
            'json' => $data
        ]);

        return $response->toArray();
    }

    /**
     * Effectue une requête DELETE vers l'API
     */
    public function delete(string $endpoint, ?string $token = null): void
    {
        $headers = [];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $this->client->request('DELETE', self::API_BASE . $endpoint, [
            'headers' => $headers
        ]);
    }

    /**
     * Effectue une requête POST multipart pour l'upload de fichiers
     */
    public function postMultipart(string $endpoint, array $data, array $files, ?string $token = null): array
    {
        $url = self::API_BASE . $endpoint;

        // Utiliser cURL pour le multipart
        $ch = curl_init();

        // Préparer les données
        $postData = [];

        // Ajouter la description
        if (isset($data['description'])) {
            $postData['description'] = $data['description'];
        }

        // Ajouter les fichiers - IMPORTANT : utiliser images[] pour chaque fichier
        $fileIndex = 0;
        foreach ($files as $file) {
            if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $pathname = $file->getPathname();
                if (!empty($pathname) && file_exists($pathname)) {
                    // Utiliser images[0], images[1], etc.
                    $postData['images[' . $fileIndex . ']'] = new \CURLFile(
                        $pathname,
                        $file->getMimeType(),
                        $file->getClientOriginalName()
                    );
                    $fileIndex++;
                }
            }
        }

        // Headers
        $headers = [
            'Authorization: Bearer ' . $token
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        if ($httpCode !== 201 && $httpCode !== 200) {
            throw new \Exception("HTTP $httpCode: " . $response);
        }

        return json_decode($response, true);
    }
}
