<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiService {
    private HttpClientInterface $client;
    private string $apiBase;

    public function __construct(HttpClientInterface $apiClient, #[Autowire(env: 'API_BASE')] string $apiBase) {
        $this->client = $apiClient;
        $this->apiBase = $apiBase;
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

        $response = $this->client->request('GET', $this->apiBase . $endpoint, [
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

        $response = $this->client->request('POST', $this->apiBase . $endpoint, [
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

        $response = $this->client->request('PUT', $this->apiBase . $endpoint, [
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

        $this->client->request('DELETE', $this->apiBase . $endpoint, [
            'headers' => $headers
        ]);
    }

    /**
     * Effectue une requête POST multipart pour l'upload de fichiers
     */
    public function postMultipart(string $endpoint, array $data, array $files, ?string $token = null): array
    {
        $url = $this->apiBase . $endpoint;

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

    /**
     * Effectue une requête PUT multipart pour l'upload de fichiers
     */
    public function putMultipart(string $endpoint, array $data, ?string $token = null): array
    {
        $url = $this->apiBase . $endpoint;

        // Utiliser cURL pour le multipart PUT
        $ch = curl_init();

        // Préparer les données
        $postData = [];

        // Ajouter l'ID (requis)
        if (isset($data['id'])) {
            $postData['id'] = $data['id'];
        }

        // Ajouter le username si présent
        if (isset($data['username']) && !empty($data['username'])) {
            $postData['username'] = $data['username'];
        }

        // Ajouter le password si présent
        if (isset($data['password']) && !empty($data['password'])) {
            $postData['password'] = $data['password'];
        }

        // Ajouter l'avatar si présent
        if (isset($data['avatar']) && $data['avatar'] instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $file = $data['avatar'];
            $pathname = $file->getPathname();
            if (!empty($pathname) && file_exists($pathname)) {
                $postData['avatar'] = new \CURLFile(
                    $pathname,
                    $file->getMimeType(),
                    $file->getClientOriginalName()
                );
            }
        }

        // Headers
        $headers = [
            'Authorization: Bearer ' . $token
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
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

        if ($httpCode !== 200) {
            throw new \Exception("HTTP $httpCode: " . $response);
        }

        return json_decode($response, true);
    }
}
