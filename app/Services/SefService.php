<?php

namespace App\Services;

use App\Models\SefEfakturaSetting;
use App\Services\Sef\Dtos\EfakturaVersionDto;
use App\Services\Sef\Dtos\MiniCompanyDto;
use App\Services\Sef\Dtos\MiniInvoiceDto;
use App\Services\Sef\Dtos\ValueAddedTaxExemptionReasonDto;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SefService
{
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
            'query' => $params,
        ];

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

        // Generate request ID for tracking
        $requestId = $this->generateRequestId();

        // Build headers
        $headers = array_merge($options['headers'] ?? [], [
            'ApiKey' => $this->apiKey,
            'X-Request-Id' => $requestId,
            'Accept' => 'application/json',
            'User-Agent' => 'Pausalci-SEF-Integration/1.0',
        ]);

        Log::info('SEF API Request', [
            'method' => $method,
            'url' => $url,
            'request_id' => $requestId,
            'user_id' => $this->sefSettings?->user_id,
            'has_api_key' => ! empty($this->apiKey),
            'content_type' => $headers['Content-Type'] ?? 'not set',
            'has_body' => isset($options['body']),
            'body_length' => isset($options['body']) ? strlen($options['body']) : 0,
        ]);

        try {
            $startTime = microtime(true);

            // Build HTTP request with Laravel Http
            $http = Http::timeout($this->timeout)
                ->connectTimeout($this->connectTimeout);

            if (! $this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            // Add headers AFTER withoutVerifying
            $http = $http->withHeaders($headers);

            // Handle different request types
            if (strtoupper($method) === 'POST' && isset($options['body'])) {
                // For POST with raw body (like XML), build URL with query params
                $fullUrl = $url;
                if (! empty($options['query'])) {
                    $fullUrl .= '?'.http_build_query($options['query']);
                }

                // Use post() with the raw body directly via Guzzle options
                $response = $http->post($fullUrl, [
                    'body' => $options['body'],
                ]);
            } else {
                $response = match (strtoupper($method)) {
                    'GET' => $http->get($url, $options['query'] ?? []),
                    'POST' => $http->post($url, array_merge($options['json'] ?? [], $options['query'] ?? [])),
                    'DELETE' => $http->delete($url, $options['json'] ?? []),
                    default => throw new \Exception("Unsupported HTTP method: $method"),
                };
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('SEF API Response', [
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
                'request_id' => $requestId,
                'user_id' => $this->sefSettings?->user_id,
            ]);

            // Handle errors
            if ($response->failed()) {
                return $this->handleHttpError($response, $requestId);
            }

            return $this->parseHttpResponse($response);

        } catch (ConnectionException $e) {
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
     * Parse the HTTP response from Laravel Http.
     */
    protected function parseHttpResponse(Response $response): array
    {
        $contentType = $response->header('Content-Type') ?? '';

        // Handle JSON responses
        if (str_contains($contentType, 'application/json')) {
            return $response->json() ?? [];
        }

        // Handle XML responses
        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            return ['xml' => $response->body(), 'content_type' => $contentType];
        }

        // Handle binary responses (PDF downloads, etc.)
        if (str_contains($contentType, 'application/octet-stream') ||
            str_contains($contentType, 'application/pdf')) {
            return ['binary' => $response->body(), 'content_type' => $contentType];
        }

        // Handle plain text responses
        if (str_contains($contentType, 'text/plain')) {
            return ['text' => $response->body(), 'content_type' => $contentType];
        }

        // Fallback: return raw response
        return ['raw' => $response->body(), 'content_type' => $contentType];
    }

    /**
     * Handle HTTP error responses.
     */
    protected function handleHttpError(Response $response, string $requestId): array
    {
        $statusCode = $response->status();
        $responseBody = $response->body();

        return [
            'error' => "SEF API request failed with status {$statusCode}: {$responseBody}",
            'type' => 'http_error',
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'request_id' => $requestId,
        ];
    }

    /**
     * Handle connection errors.
     */
    protected function handleConnectionError(ConnectionException $e, string $requestId): array
    {
        $message = $e->getMessage();

        return [
            'error_type' => 'ConnectionException',
            'message' => 'SEF API connection failed: '.$message,
            'request_id' => $requestId,
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
        $response = $e->response;
        $statusCode = $response ? $response->status() : 0;
        $responseBody = $response ? $response->body() : 'No response body';

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
     * Search for a company by PIB in SEF system.
     * Returns array with companies key for consistency with verification command.
     */
    public function searchCompanyByPib(string $pib): array
    {
        $result = $this->checkCompanyExistsByPib($pib);

        if (isset($result['error'])) {
            return $result;
        }

        // Return in format expected by verification command
        if ($result['exists']) {
            return [
                'companies' => [$result['company']],
            ];
        }

        return [
            'companies' => [],
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

    /**
     * 7. Send invoice UBL XML to SEF eFaktura system.
     * This uploads the invoice UBL XML to the SEF system.
     */
    public function sendInvoiceUbl(string $xmlContent, array $params = []): array
    {
        // Build endpoint with query parameters
        $endpoint = 'publicApi/sales-invoice/ubl';

        // Send XML directly as application/xml
        $options = [
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
            'body' => $xmlContent,
        ];

        if (! empty($params)) {
            $options['query'] = $params;
        }

        $response = $this->request('POST', $endpoint, $options);

        // Log full response for debugging
        Log::info('eFaktura send response details', [
            'response' => $response,
            'has_error' => isset($response['error']),
            'sales_invoice_id' => $response['SalesInvoiceId'] ?? null,
        ]);

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }

    /**
     * Send invoice to eFaktura by uploading UBL XML content.
     * This is the main method to send invoices to the SEF system.
     */
    public function sendInvoice(string $xmlContent, ?string $sendToCir = null, ?string $requestId = null): array
    {
        $params = [];

        if ($sendToCir !== null) {
            $params['sendToCir'] = $sendToCir;
        }

        if ($requestId !== null) {
            $params['requestId'] = $requestId;
        }

        // executeValidation can be added if needed
        $params['executeValidation'] = true;

        return $this->sendInvoiceUbl($xmlContent, $params);
    }

    /**
     * Get list of sent invoices from SEF system.
     */
    public function getSentInvoices(array $filters = []): array
    {
        $response = $this->get('publicApi/sales-invoice/sent', $filters);

        if (isset($response['error'])) {
            return $response;
        }

        // Convert to DTOs if we have data
        if (is_array($response)) {
            $invoices = [];
            foreach ($response as $invoiceData) {
                $invoices[] = MiniInvoiceDto::fromArray($invoiceData);
            }

            return ['invoices' => $invoices];
        }

        return $response;
    }

    /**
     * Get invoice status from SEF system by SEF invoice ID.
     */
    public function getInvoiceStatus(string $sefInvoiceId): array
    {
        $response = $this->get('publicApi/sales-invoice', [
            'invoiceId' => $sefInvoiceId,
        ]);

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }

    /**
     * Cancel an invoice in the SEF system.
     */
    public function cancelInvoice(string $sefInvoiceId, string $reason = ''): array
    {
        $response = $this->delete("publicApi/sales-invoice/{$sefInvoiceId}", [
            'reason' => $reason,
        ]);

        if (isset($response['error'])) {
            return $response;
        }

        return $response;
    }
}
