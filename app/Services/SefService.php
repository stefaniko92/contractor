<?php

namespace App\Services;

use App\Models\SefEfakturaSetting;
use App\Services\Sef\Dtos\EfakturaVersionDto;
use App\Services\Sef\Dtos\MiniCompanyDto;
use App\Services\Sef\Dtos\ValueAddedTaxExemptionReasonDto;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class SefService
{
    protected Client $client;

    protected ?string $apiKey;

    protected string $baseUrl;

    protected int $timeout;

    protected int $connectTimeout;

    protected bool $verifySsl;

    protected ?SefEfakturaSetting $sefSettings;

    public function __construct(?int $userId = null)
    {
        $this->baseUrl = config('services.sef.base_url', 'https://suf.purs.gov.rs/api');
        $this->timeout = config('services.sef.timeout', 30);
        $this->connectTimeout = config('services.sef.connect_timeout', 10);
        $this->verifySsl = config('services.sef.verify_ssl', true);

        $this->initializeForUser($userId);
    }

    /**
     * Initialize the service for a specific user.
     */
    protected function initializeForUser(?int $userId): void
    {
        $targetUserId = $userId ?? Auth::id();

        if (! $targetUserId) {
            $this->apiKey = null;
            $this->sefSettings = null;

            return;
        }

        $this->sefSettings = SefEfakturaSetting::where('user_id', $targetUserId)->first();

        if (! $this->sefSettings || ! $this->sefSettings->is_enabled) {
            $this->apiKey = null;

            return;
        }

        $this->apiKey = $this->sefSettings->api_key;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify' => $this->verifySsl,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'Pausalci-SEF-Integration/1.0',
            ],
        ]);
    }

    /**
     * Create a new instance for a specific user.
     */
    public static function forUser(int $userId): self
    {
        return new self($userId);
    }

    /**
     * Create a new instance using the authenticated user.
     */
    public static function forAuthenticatedUser(): self
    {
        return new self;
    }

    /**
     * Make a GET request to the SEF API.
     */
    protected function get(string $endpoint, array $params = [], array $headers = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $params,
            'headers' => $headers,
        ]);
    }

    /**
     * Make a POST request to the SEF API.
     */
    protected function post(string $endpoint, array $data = [], array $params = [], array $headers = []): array
    {
        $options = [
            'headers' => $headers,
        ];

        if (! empty($params)) {
            $options['query'] = $params;
        }

        if (! empty($data)) {
            $options['json'] = $data;
        }

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Make a DELETE request to the SEF API.
     */
    protected function delete(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('DELETE', $endpoint, [
            'json' => $data,
            'headers' => $headers,
        ]);
    }

    /**
     * Upload a file to the SEF API.
     */
    protected function upload(string $endpoint, array $files, array $params = [], array $headers = []): array
    {
        $multipart = [];

        foreach ($files as $name => $contents) {
            $multipart[] = [
                'name' => $name,
                'contents' => $contents,
                'filename' => is_string($name) ? $name : 'file',
            ];
        }

        $options = [
            'multipart' => $multipart,
            'headers' => $headers,
        ];

        if (! empty($params)) {
            $options['query'] = $params;
        }

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Make an HTTP request to the SEF API.
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        // Check if SEF is configured and enabled for the user
        if (! $this->isConfigured()) {
            return [
                'error' => 'SEF integracija nije konfigurisana ili omogućena za ovog korisnika',
                'type' => 'configuration_error',
                'configuration_status' => $this->getConfigurationStatus(),
            ];
        }

        $url = $this->baseUrl.'/'.ltrim($endpoint, '/');

        // Add API key to headers
        $options['headers']['ApiKey'] = $this->apiKey;

        // Generate request ID for tracking
        $requestId = $this->generateRequestId();
        $options['headers']['X-Request-Id'] = $requestId;

        Log::info('SEF API Request', [
            'method' => $method,
            'url' => $url,
            'request_id' => $requestId,
            'user_id' => $this->sefSettings?->user_id,
            'has_api_key' => ! empty($this->apiKey),
        ]);

        try {
            $startTime = microtime(true);

            $response = $this->client->request($method, $endpoint, $options);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $responseBody = $response->getBody()->getContents();

            Log::info('SEF API Response', [
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'response_size' => strlen($responseBody),
                'request_id' => $requestId,
                'user_id' => $this->sefSettings?->user_id,
            ]);

            return $this->parseResponse($response, $responseBody);

        } catch (ConnectException $e) {
            $error = $this->handleConnectionError($e, $requestId);
            Log::error('SEF API Connection Error', $error);

            return ['error' => $error['message'], 'type' => 'connection_error'];

        } catch (RequestException $e) {
            $error = $this->handleRequestError($e, $requestId);
            Log::error('SEF API Request Error', $error);

            return ['error' => $error['message'], 'type' => 'request_error'];

        } catch (\Exception $e) {
            $error = $this->handleUnexpectedError($e, $requestId);
            Log::error('SEF API Unexpected Error', $error);

            return ['error' => $error['message'], 'type' => 'unexpected_error'];
        }
    }

    /**
     * Parse the API response.
     */
    protected function parseResponse(ResponseInterface $response, string $responseBody): array
    {
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');

        // Handle different content types
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: '.json_last_error_msg());
            }

            return $data ?? [];
        }

        // Handle XML responses (for SEF XML endpoints)
        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            return ['xml' => $responseBody, 'content_type' => $contentType];
        }

        // Handle binary responses (PDF downloads, etc.)
        if (str_contains($contentType, 'application/octet-stream') ||
            str_contains($contentType, 'application/pdf')) {
            return ['binary' => $responseBody, 'content_type' => $contentType];
        }

        // Handle plain text responses
        if (str_contains($contentType, 'text/plain')) {
            return ['text' => $responseBody, 'content_type' => $contentType];
        }

        // Fallback: return raw response
        return ['raw' => $responseBody, 'content_type' => $contentType];
    }

    /**
     * Handle connection errors.
     */
    protected function handleConnectionError(ConnectException $e, string $requestId): array
    {
        $message = $e->getMessage();

        return [
            'error_type' => 'ConnectException',
            'message' => 'SEF API connection failed: '.$message,
            'request_id' => $requestId,
            'curl_error' => $this->extractCurlError($message),
            'suggestions' => [
                'Check if the SEF service is accessible',
                'Verify network connectivity',
                'Check API configuration',
                'Consider adjusting timeout settings',
            ],
        ];
    }

    /**
     * Handle HTTP request errors.
     */
    protected function handleRequestError(RequestException $e, string $requestId): array
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;
        $responseBody = $response ? $response->getBody()->getContents() : 'No response body';

        return [
            'error_type' => 'RequestException',
            'message' => "SEF API request failed with status {$statusCode}: ".$e->getMessage(),
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'request_id' => $requestId,
        ];
    }

    /**
     * Handle unexpected errors.
     */
    protected function handleUnexpectedError(\Exception $e, string $requestId): array
    {
        return [
            'error_type' => get_class($e),
            'message' => 'SEF API unexpected error: '.$e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_id' => $requestId,
            'trace' => $e->getTraceAsString(),
        ];
    }

    /**
     * Generate a unique request ID for tracking.
     */
    protected function generateRequestId(): string
    {
        return uniqid('sef_', true);
    }

    /**
     * Extract cURL error details from error message.
     */
    protected function extractCurlError(string $message): array
    {
        $curlInfo = [];

        // Extract cURL error number
        if (preg_match('/cURL error (\d+):/', $message, $matches)) {
            $curlInfo['error_code'] = (int) $matches[1];
            $curlInfo['error_description'] = $this->getCurlErrorDescription($curlInfo['error_code']);
        }

        return $curlInfo;
    }

    /**
     * Get human-readable description for common cURL error codes.
     */
    protected function getCurlErrorDescription(int $errorCode): string
    {
        $descriptions = [
            6 => 'Could not resolve host (DNS lookup failed)',
            7 => 'Failed to connect to host',
            28 => 'Operation timeout',
            35 => 'SSL connect error',
            51 => 'SSL peer certificate verification failed',
            52 => 'Got nothing from server',
            56 => 'Connection reset by peer',
            60 => 'SSL certificate has expired',
            61 => 'SSL certificate is not yet valid',
        ];

        return $descriptions[$errorCode] ?? 'Unknown cURL error';
    }

    /**
     * Check if the service is properly configured and enabled for the user.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) &&
               $this->sefSettings &&
               $this->sefSettings->is_enabled;
    }

    /**
     * Check if SEF is enabled for the user.
     */
    public function isEnabled(): bool
    {
        return $this->sefSettings && $this->sefSettings->is_enabled;
    }

    /**
     * Get the user's SEF settings.
     */
    public function getSefSettings(): ?SefEfakturaSetting
    {
        return $this->sefSettings;
    }

    /**
     * Get configuration status for debugging.
     */
    public function getConfigurationStatus(): array
    {
        return [
            'user_id' => $this->sefSettings?->user_id,
            'sef_enabled' => $this->isEnabled(),
            'api_key_set' => ! empty($this->apiKey),
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify_ssl' => $this->verifySsl,
            'environment' => app()->environment(),
            'default_vat_exemption' => $this->sefSettings?->default_vat_exemption,
            'default_vat_category' => $this->sefSettings?->default_vat_category,
            'webhook_url' => $this->sefSettings?->webhook_url,
        ];
    }

    /**
     * Get user's default VAT exemption reason.
     */
    public function getDefaultVatExemption(): ?string
    {
        return $this->sefSettings?->default_vat_exemption;
    }

    /**
     * Get user's default VAT category.
     */
    public function getDefaultVatCategory(): ?string
    {
        return $this->sefSettings?->default_vat_category;
    }

    /**
     * Check if SEF is available and provide user-friendly status.
     */
    public function getAvailabilityStatus(): array
    {
        if (! $this->sefSettings) {
            return [
                'available' => false,
                'message' => 'SEF podešavanja nisu pronađena. Molimo konfigurišite SEF integraciju.',
                'action_required' => 'Konfigurišite SEF podešavanja',
                'type' => 'settings_not_found',
            ];
        }

        if (! $this->sefSettings->is_enabled) {
            return [
                'available' => false,
                'message' => 'SEF integracija je onemogućena. Omogućite je u podešavanjima.',
                'action_required' => 'Omogućite SEF integraciju',
                'type' => 'disabled',
            ];
        }

        if (empty($this->sefSettings->api_key)) {
            return [
                'available' => false,
                'message' => 'API ključ nije postavljen. Unesite vaš API ključ u podešavanjima.',
                'action_required' => 'Unesite API ključ',
                'type' => 'missing_api_key',
            ];
        }

        return [
            'available' => true,
            'message' => 'SEF integracija je spremna za korišćenje.',
            'action_required' => null,
            'type' => 'available',
        ];
    }

    // ========================================
    // SEF API ENDPOINTS
    // ========================================

    /**
     * 4. Get unit of measures from SEF system.
     */
    public function getUnitMeasures(): array
    {
        $response = $this->get('publicApi/get-unit-measures');

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }

    /**
     * 5. Get all registered companies from SEF system.
     * This can be used to verify if a client exists in SEF.
     */
    public function getAllCompanies(bool $includeAllStatuses = false): array
    {
        $response = $this->get('publicApi/getAllCompanies', [
            'includeAllStatuses' => $includeAllStatuses,
        ]);

        if (isset($response['error'])) {
            return $response;
        }

        // Convert to DTOs if we have data
        if (is_array($response)) {
            $companies = [];
            foreach ($response as $companyData) {
                $companies[] = MiniCompanyDto::fromArray($companyData);
            }

            return ['companies' => $companies];
        }

        return $response;
    }

    /**
     * 6. Download all companies list from SEF system.
     */
    public function downloadAllCompanies(bool $includeAllStatuses = false): array
    {
        $response = $this->get('publicApi/downloadAllCompanies', [
            'includeAllStatuses' => $includeAllStatuses,
        ]);

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }

    /**
     * 21. Get eFaktura version information.
     */
    public function getEfakturaVersion(): array
    {
        $response = $this->get('publicApi/getEfakturaVersion');

        if (isset($response['error'])) {
            return $response;
        }

        // Convert to DTO
        if (is_array($response)) {
            return ['version_info' => EfakturaVersionDto::fromArray($response)];
        }

        return $response;
    }

    /**
     * 17. Get list of all VAT exemption reasons.
     */
    public function getValueAddedTaxExemptionReasonList(): array
    {
        $response = $this->get('publicApi/sales-invoice/getValueAddedTaxExemptionReasonList');

        if (isset($response['error'])) {
            return $response;
        }

        // Convert to DTOs if we have data
        if (is_array($response)) {
            $reasons = [];
            foreach ($response as $reasonData) {
                $reasons[] = ValueAddedTaxExemptionReasonDto::fromArray($reasonData);
            }

            return ['exemption_reasons' => $reasons];
        }

        return $response;
    }

    /**
     * 20. Subscribe for invoice status change notifications.
     */
    public function subscribeToNotifications(): array
    {
        $response = $this->post('publicApi/subscribe');

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }

    /**
     * Check if a company exists in SEF system by tax identifier (PIB).
     */
    public function checkCompanyExistsByPib(string $pib): array
    {
        $companiesResponse = $this->getAllCompanies();

        if (isset($companiesResponse['error'])) {
            return $companiesResponse;
        }

        $companies = $companiesResponse['companies'] ?? [];

        // Search for company by PIB
        foreach ($companies as $company) {
            if ($company->getPib() === $pib && $company->isSefEnabled()) {
                return [
                    'exists' => true,
                    'company' => $company,
                    'message' => 'Kompanija je pronađena i aktivna u SEF sistemu',
                ];
            }
        }

        return [
            'exists' => false,
            'company' => null,
            'message' => 'Kompanija nije pronađena ili nije aktivna u SEF sistemu',
        ];
    }

    /**
     * Check if a company exists in SEF system by name.
     */
    public function checkCompanyExistsByName(string $companyName): array
    {
        $companiesResponse = $this->getAllCompanies();

        if (isset($companiesResponse['error'])) {
            return $companiesResponse;
        }

        $companies = $companiesResponse['companies'] ?? [];

        // Search for company by name (case-insensitive)
        foreach ($companies as $company) {
            if (strcasecmp($company->name, $companyName) === 0 && $company->isSefEnabled()) {
                return [
                    'exists' => true,
                    'company' => $company,
                    'message' => 'Kompanija je pronađena i aktivna u SEF sistemu',
                ];
            }
        }

        return [
            'exists' => false,
            'company' => null,
            'message' => 'Kompanija nije pronađena ili nije aktivna u SEF sistemu',
        ];
    }

    /**
     * Search companies in SEF system by various criteria.
     */
    public function searchCompanies(array $criteria): array
    {
        $companiesResponse = $this->getAllCompanies(true); // Include all statuses for search

        if (isset($companiesResponse['error'])) {
            return $companiesResponse;
        }

        $companies = $companiesResponse['companies'] ?? [];
        $filteredCompanies = [];

        foreach ($companies as $company) {
            $matches = true;

            // Filter by PIB
            if (isset($criteria['pib']) && $company->getPib() !== $criteria['pib']) {
                $matches = false;
            }

            // Filter by name (partial match)
            if (isset($criteria['name']) && stripos($company->name, $criteria['name']) === false) {
                $matches = false;
            }

            // Filter by SEF enabled status
            if (isset($criteria['sef_enabled']) && $company->isSefEnabled() !== $criteria['sef_enabled']) {
                $matches = false;
            }

            // Filter by VAT registration
            if (isset($criteria['vat_registered']) && $company->isRegisteredForVat !== $criteria['vat_registered']) {
                $matches = false;
            }

            if ($matches) {
                $filteredCompanies[] = $company;
            }
        }

        return [
            'companies' => $filteredCompanies,
            'total_count' => count($filteredCompanies),
            'criteria' => $criteria,
        ];
    }
}
