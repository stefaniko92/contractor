<?php

namespace App\Services;

use GuzzleHttp\Client;

class ExchangeRateService
{
    protected $client;

    protected $username;

    protected $password;

    protected $licenceID;

    protected $url;

    public function __construct()
    {
        $this->client = new Client;
        $this->username = config('services.nbs.username');
        $this->password = config('services.nbs.password');
        $this->licenceID = config('services.nbs.licence_id');
        $this->url = config('services.nbs.url', 'https://webservices.nbs.rs/CommunicationOfficeService1_0/ExchangeRateXmlService.asmx');
    }

    /**
     * Fetch the current exchange rate list from NBS.
     */
    public function fetchCurrentExchangeRates(): array
    {
        $headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
        ];

        $body = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tns="http://communicationoffice.nbs.rs">
        <soap:Header>
            <AuthenticationHeader xmlns="http://communicationoffice.nbs.rs">
                <UserName>'.$this->username.'</UserName>
                <Password>'.$this->password.'</Password>
                <LicenceID>'.$this->licenceID.'</LicenceID>
            </AuthenticationHeader>
        </soap:Header>
        <soap:Body>
            <GetCurrentExchangeRate xmlns="http://communicationoffice.nbs.rs">
                <exchangeRateListTypeID>3</exchangeRateListTypeID>
            </GetCurrentExchangeRate>
        </soap:Body>
    </soap:Envelope>';

        \Log::info('Attempting to fetch exchange rates from NBS', [
            'url' => $this->url,
            'ssl_verify' => $this->shouldVerifySSL(),
            'username' => $this->username ? 'SET' : 'NOT_SET',
            'password' => $this->password ? 'SET' : 'NOT_SET',
            'licence_id' => $this->licenceID ? 'SET' : 'NOT_SET',
        ]);

        try {
            $startTime = microtime(true);

            $response = $this->client->post($this->url, [
                'headers' => $headers,
                'body' => $body,
                'verify' => $this->shouldVerifySSL(),
                'timeout' => 30, // 30 second timeout
                'connect_timeout' => 10, // 10 second connection timeout
            ]);

            $duration = round((microtime(true) - $startTime) * 1000, 2); // Duration in milliseconds
            $responseBody = $response->getBody()->getContents();

            \Log::info('Exchange rates request successful', [
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'response_size' => strlen($responseBody),
                'url' => $this->url,
            ]);

            \Log::debug('SOAP Response', ['response_body' => $responseBody]);

            return $this->parseSoapResponse($responseBody);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            \Log::error('Connection error fetching exchange rates', [
                'error_type' => 'ConnectException',
                'message' => $e->getMessage(),
                'url' => $this->url,
                'ssl_verify' => $this->shouldVerifySSL(),
                'curl_error' => $this->extractCurlError($e->getMessage()),
                'suggestions' => [
                    'Check if the NBS service is accessible',
                    'Verify network connectivity',
                    'Consider adjusting SSL verification settings',
                    'Check if there are firewall restrictions',
                ],
            ]);

            return ['error' => 'Connection failed: '.$e->getMessage()];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';

            \Log::error('HTTP error fetching exchange rates', [
                'error_type' => 'RequestException',
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
                'url' => $this->url,
                'response_body' => $responseBody,
                'request_headers' => $headers,
            ]);

            return ['error' => "HTTP {$statusCode} error: ".$e->getMessage()];
        } catch (\Exception $e) {
            \Log::error('Unexpected error fetching exchange rates', [
                'error_type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $this->url,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['error' => 'Unexpected error: '.$e->getMessage()];
        }
    }

    /**
     * Parse the SOAP response to extract exchange rates.
     *
     * @param  string  $responseBody
     * @return array
     */

    /**
     * Determine if SSL verification should be enabled.
     */
    protected function shouldVerifySSL(): bool
    {
        return app()->environment('production'); // Enable SSL verification only in production
    }

    /**
     * Parse the SOAP response to extract exchange rates.
     */
    protected function parseSoapResponse(string $responseBody): array
    {
        // Load the response as XML
        $xml = simplexml_load_string($responseBody);
        if ($xml === false) {
            \Log::error('Failed to parse SOAP response as XML.', ['response_body' => $responseBody]);

            return ['error' => 'Failed to parse SOAP response as XML.'];
        }

        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $xml->registerXPathNamespace('nbs', 'http://communicationoffice.nbs.rs');

        // Extract the content of <GetCurrentExchangeRateResult>
        $resultElement = $xml->xpath('//soap:Body/nbs:GetCurrentExchangeRateResponse/nbs:GetCurrentExchangeRateResult');
        if (empty($resultElement)) {
            \Log::error('No <GetCurrentExchangeRateResult> element found in SOAP response.', ['response_body' => $responseBody]);

            return ['error' => 'No exchange rates found in the SOAP response.'];
        }

        $resultXmlString = html_entity_decode((string) $resultElement[0]);
        if (empty($resultXmlString)) {
            \Log::error('The <GetCurrentExchangeRateResult> element is empty.', ['response_body' => $responseBody]);

            return ['error' => 'No exchange rates data found in the response.'];
        }

        // Parse the inner XML of <GetCurrentExchangeRateResult>
        $innerXml = simplexml_load_string($resultXmlString);
        if ($innerXml === false) {
            \Log::error('Failed to parse inner XML of <GetCurrentExchangeRateResult>.', ['inner_xml' => $resultXmlString]);

            return ['error' => 'Failed to parse exchange rates data.'];
        }

        $rates = [];
        foreach ($innerXml->ExchangeRate as $rateElement) {
            $rates[] = [
                'currency_code' => (string) $rateElement->CurrencyCodeAlfaChar,
                'currency_name' => (string) $rateElement->CurrencyNameEng,
                'middle_rate' => (float) $rateElement->MiddleRate,
                'date' => (string) $rateElement->Date,
            ];
        }

        return $rates;
    }

    /**
     * Extract cURL error details from error message
     */
    protected function extractCurlError(string $message): array
    {
        $curlInfo = [];

        // Extract cURL error number
        if (preg_match('/cURL error (\d+):/', $message, $matches)) {
            $curlInfo['error_code'] = (int) $matches[1];
            $curlInfo['error_description'] = $this->getCurlErrorDescription($curlInfo['error_code']);
        }

        // Extract URL from message
        if (preg_match('/for (.+)$/', $message, $matches)) {
            $curlInfo['failed_url'] = trim($matches[1]);
        }

        return $curlInfo;
    }

    /**
     * Get human-readable description for common cURL error codes
     */
    protected function getCurlErrorDescription(int $errorCode): string
    {
        $descriptions = [
            6 => 'Could not resolve host',
            7 => 'Failed to connect to host',
            28 => 'Operation timeout',
            35 => 'SSL connect error',
            51 => 'SSL peer certificate or SSH remote key was not OK',
            52 => 'Got nothing from server',
            56 => 'Recv failure: Connection reset by peer',
            60 => 'SSL certificate problem: certificate has expired',
        ];

        return $descriptions[$errorCode] ?? 'Unknown cURL error';
    }
}
