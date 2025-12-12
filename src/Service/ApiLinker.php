<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiLinker
{
	private $baseURL;
	private $client;

	public function __construct(HttpClientInterface $client, string $apiServerUrl)
	{
		$this->baseURL = $apiServerUrl;
		$this->client = $client->withOptions([
			'no_proxy' => '127.0.0.1',
			'verify_peer' => false
		]);
	}

	public function postData($url, $data, $token)
	{
		// Construire les options de base
		$options = [
			'body' => $data
		];

		// N'ajouter le header Authorization QUE si le token existe
		if ($token !== null && $token !== '') {
			$options['headers'] = [
				'Authorization' => 'Bearer ' . $token
			];
		}

		$response = $this->client->request('POST', $this->baseURL . $url, $options);
		$content = $response->getContent();
		return $content;
	}

	public function getData($url, $token)
	{
		// Construire les options de base
		$options = [];

		// N'ajouter le header Authorization QUE si le token existe
		if ($token !== null && $token !== '') {
			$options['headers'] = [
				'Authorization' => 'Bearer ' . $token
			];
		}

		$response = $this->client->request('GET', $this->baseURL . $url, $options);
		$content = $response->getContent();
		return $content;
	}

	public function putData($url, $data, $token)
	{
		// Construire les options de base
		$options = [
			'body' => $data
		];

		// N'ajouter le header Authorization QUE si le token existe
		if ($token !== null && $token !== '') {
			$options['headers'] = [
				'Authorization' => 'Bearer ' . $token
			];
		}

		$response = $this->client->request('PUT', $this->baseURL . $url, $options);
		$content = $response->getContent();
		return $content;
	}

	public function deleteData($url, $token)
	{
		// Construire les options de base
		$options = [];

		// N'ajouter le header Authorization QUE si le token existe
		if ($token !== null && $token !== '') {
			$options['headers'] = [
				'Authorization' => 'Bearer ' . $token
			];
		}

		$response = $this->client->request('DELETE', $this->baseURL . $url, $options);
		$content = $response->getContent();
		return $content;
	}
}
