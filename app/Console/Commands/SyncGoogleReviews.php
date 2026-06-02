<?php

namespace App\Console\Commands;

use App\Models\Testimonial;
use Illuminate\Console\Command;

class SyncGoogleReviews extends Command
{
    // protected $signature = 'google:sync-reviews
    //                         {--force-refresh : Força nova busca de accountId e locationId mesmo que estejam em cache}';

    // protected $description = 'Sincroniza reviews do Google Business Profile para a tabela testimonials';

    // // ── Google OAuth2 ─────────────────────────────────────────────────────────
    // private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    // private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    // private const SCOPE     = 'https://www.googleapis.com/auth/business.manage';

    // // ── API endpoints ─────────────────────────────────────────────────────────
    // private const API_ACCOUNTS  = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';
    // private const API_LOCATIONS = 'https://mybusinessbusinessinformation.googleapis.com/v1/{account}/locations?readMask=name,title';
    // private const API_REVIEWS   = 'https://mybusiness.googleapis.com/v4/{account}/{location}/reviews';

    // // ── Star rating map ───────────────────────────────────────────────────────
    // private const STAR_MAP = [
    //     'STAR_RATING_UNSPECIFIED' => 0.0,
    //     'ONE'   => 1.0,
    //     'TWO'   => 2.0,
    //     'THREE' => 3.0,
    //     'FOUR'  => 4.0,
    //     'FIVE'  => 5.0,
    // ];

    // // ── Rate limit ────────────────────────────────────────────────────────────
    // private const RETRY_DELAYS = [5, 15, 30]; // segundos entre retries em 429

    // private string $cacheFile;
    // private array  $cache = [];

    // // ── Entry point ───────────────────────────────────────────────────────────

    // public function handle(): int
    // {
    //     $this->cacheFile = storage_path('app/google_oauth_cache.json');
    //     $this->loadCache();

    //     $clientId     = config('services.google.client_id');
    //     $clientSecret = config('services.google.client_secret');
    //     $refreshToken = config('services.google.refresh_token');

    //     if (! $clientId || ! $clientSecret || ! $refreshToken) {
    //         $this->error('[Google] Credenciais em falta. Verifica GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET e GOOGLE_REFRESH_TOKEN no .env');
    //         return self::FAILURE;
    //     }

    //     // ── 1. Access token ───────────────────────────────────────────────────
    //     $accessToken = $this->getAccessToken($clientId, $clientSecret, $refreshToken);
    //     if (! $accessToken) {
    //         $this->error('[Google] Não foi possível obter access token.');
    //         return self::FAILURE;
    //     }

    //     // ── 2. Account ID (cached) ────────────────────────────────────────────
    //     $accountId = $this->getAccountId($accessToken);
    //     if (! $accountId) {
    //         $this->error('[Google] Não foi possível obter accountId.');
    //         return self::FAILURE;
    //     }

    //     // ── 3. Location ID (cached) ───────────────────────────────────────────
    //     $locationId = $this->getLocationId($accessToken, $accountId);
    //     if (! $locationId) {
    //         $this->error('[Google] Não foi possível obter locationId.');
    //         return self::FAILURE;
    //     }

    //     // ── 4. Reviews ────────────────────────────────────────────────────────
    //     $reviews = $this->fetchAllReviews($accessToken, $accountId, $locationId);
    //     if ($reviews === null) {
    //         return self::FAILURE;
    //     }

    //     // ── 5. Persist ────────────────────────────────────────────────────────
    //     [$imported, $skipped] = $this->saveReviews($reviews);

    //     $this->info("[Google] Concluído — {$imported} novos / {$skipped} já existentes (total: " . count($reviews) . ')');

    //     return self::SUCCESS;
    // }

    // // ── OAuth2 ────────────────────────────────────────────────────────────────

    // private function getAccessToken(string $clientId, string $clientSecret, string $refreshToken): ?string
    // {
    //     // Usa token em cache se ainda não expirou (margem de 60s)
    //     if (
    //         ! empty($this->cache['access_token']) &&
    //         ! empty($this->cache['token_expires_at']) &&
    //         time() < ($this->cache['token_expires_at'] - 60)
    //     ) {
    //         $this->line('[Google] Access token em cache válido.');
    //         return $this->cache['access_token'];
    //     }

    //     $this->line('[Google] A renovar access token...');

    //     $payload = [
    //         'client_id'     => $clientId,
    //         'client_secret' => $clientSecret,
    //         'refresh_token' => $refreshToken,
    //         'grant_type'    => 'refresh_token',
    //     ];

    //     $response = $this->curlPost(self::TOKEN_URL, $payload);

    //     if (! $response || empty($response['access_token'])) {
    //         $this->error('[Google] Falha ao renovar token: ' . json_encode($response));
    //         return null;
    //     }

    //     $this->cache['access_token']    = $response['access_token'];
    //     $this->cache['token_expires_at'] = time() + ($response['expires_in'] ?? 3600);
    //     $this->saveCache();

    //     $this->info('[Google] Access token renovado com sucesso.');
    //     return $this->cache['access_token'];
    // }

    // // ── Account ───────────────────────────────────────────────────────────────

    // private function getAccountId(string $accessToken): ?string
    // {
    //     $force = $this->option('force-refresh');

    //     if (! $force && ! empty($this->cache['account_id'])) {§
    //         $this->line('[Google] AccountId em cache: ' . $this->cache['account_id']);
    //         return $this->cache['account_id'];
    //     }

    //     $this->line('[Google] A buscar accountId na API...');

    //     $data = $this->curlGet(self::API_ACCOUNTS, $accessToken);
    //     if ($data === null) return null;

    //     $accountName = $data['accounts'][0]['name'] ?? null;
    //     if (! $accountName) {
    //         $this->error('[Google] Nenhuma conta encontrada na resposta: ' . json_encode($data));
    //         return null;
    //     }

    //     // "accounts/123456789" → "accounts/123456789" (usamos o name completo nas URLs)
    //     $this->cache['account_id'] = $accountName;
    //     $this->saveCache();

    //     $this->info('[Google] AccountId obtido: ' . $accountName);
    //     return $accountName;
    // }

    // // ── Location ──────────────────────────────────────────────────────────────

    // private function getLocationId(string $accessToken, string $accountId): ?string
    // {
    //     $force = $this->option('force-refresh');

    //     if (! $force && ! empty($this->cache['location_id'])) {
    //         $this->line('[Google] LocationId em cache: ' . $this->cache['location_id']);
    //         return $this->cache['location_id'];
    //     }

    //     $this->line('[Google] A buscar locationId na API...');

    //     $url  = str_replace('{account}', $accountId, self::API_LOCATIONS);
    //     $data = $this->curlGet($url, $accessToken);
    //     if ($data === null) return null;

    //     $locationName = $data['locations'][0]['name'] ?? null;
    //     if (! $locationName) {
    //         $this->error('[Google] Nenhuma localização encontrada: ' . json_encode($data));
    //         return null;
    //     }

    //     $this->cache['location_id'] = $locationName;
    //     $this->saveCache();

    //     $this->info('[Google] LocationId obtido: ' . $locationName);
    //     return $locationName;
    // }

    // // ── Reviews ───────────────────────────────────────────────────────────────

    // private function fetchAllReviews(string $accessToken, string $accountId, string $locationId): ?array
    // {
    //     $this->line('[Google] A obter reviews...');

    //     $allReviews = [];
    //     $pageToken  = null;

    //     do {
    //         $url = str_replace(['{account}', '{location}'], [$accountId, $locationId], self::API_REVIEWS);
    //         $url .= '?pageSize=50';
    //         if ($pageToken) {
    //             $url .= '&pageToken=' . urlencode($pageToken);
    //         }

    //         $data = $this->curlGet($url, $accessToken);
    //         if ($data === null) return null;

    //         $batch = $data['reviews'] ?? [];
    //         $allReviews = array_merge($allReviews, $batch);

    //         $pageToken = $data['nextPageToken'] ?? null;
    //     } while ($pageToken);

    //     $this->line('[Google] ' . count($allReviews) . ' reviews obtidas.');
    //     return $allReviews;
    // }

    // // ── Persist ───────────────────────────────────────────────────────────────

    // private function saveReviews(array $reviews): array
    // {
    //     $imported = 0;
    //     $skipped  = 0;

    //     foreach ($reviews as $review) {
    //         $googleId = $review['reviewId'] ?? null;
    //         if (! $googleId) continue;

    //         // Evita duplicados
    //         if (Testimonial::where('google_review_id', $googleId)->exists()) {
    //             $skipped++;
    //             continue;
    //         }

    //         $starKey = $review['starRating'] ?? 'STAR_RATING_UNSPECIFIED';
    //         $rating  = self::STAR_MAP[$starKey] ?? 0.0;

    //         $comment    = $review['comment'] ?? '';
    //         $authorName = $review['reviewer']['displayName'] ?? 'Anónimo';
    //         $createTime = $review['createTime'] ?? null;

    //         Testimonial::create([
    //             'google_review_id' => $googleId,
    //             'name'             => $authorName,
    //             'rating'           => $rating,
    //             'comment'          => $comment,
    //             'origin'           => 'google',
    //             'published'        => false, // revisão manual antes de publicar
    //             'review_date'      => $createTime ? date('Y-m-d', strtotime($createTime)) : null,
    //         ]);

    //         $imported++;
    //     }

    //     return [$imported, $skipped];
    // }

    // // ── cURL helpers ──────────────────────────────────────────────────────────

    // /**
    //  * GET request com Bearer token e retry automático em 401 / 429.
    //  */
    // private function curlGet(string $url, string $accessToken, int $attempt = 0): ?array
    // {
    //     $ch = curl_init();
    //     curl_setopt_array($ch, [
    //         CURLOPT_URL            => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_TIMEOUT        => 30,
    //         CURLOPT_HTTPHEADER     => [
    //             'Authorization: Bearer ' . $accessToken,
    //             'Accept: application/json',
    //         ],
    //     ]);

    //     $body       = curl_exec($ch);
    //     $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $curlError  = curl_error($ch);
    //     curl_close($ch);

    //     if ($curlError) {
    //         $this->error("[cURL] Erro de rede em GET {$url}: {$curlError}");
    //         return null;
    //     }

    //     $data = json_decode($body, true);

    //     // ── 429 Rate limit ─────────────────────────────────────────────────
    //     if ($httpStatus === 429) {
    //         if ($attempt < count(self::RETRY_DELAYS)) {
    //             $delay = self::RETRY_DELAYS[$attempt];
    //             $this->warn("[Google] Rate limit (429). A aguardar {$delay}s antes de nova tentativa...");
    //             sleep($delay);
    //             return $this->curlGet($url, $accessToken, $attempt + 1);
    //         }
    //         $this->error('[Google] Rate limit persistente após ' . count(self::RETRY_DELAYS) . ' tentativas. Abandona.');
    //         return null;
    //     }

    //     // ── 401 Unauthorized ───────────────────────────────────────────────
    //     if ($httpStatus === 401) {
    //         $this->error('[Google] 401 Unauthorized — token pode estar inválido ou revogado.');
    //         return null;
    //     }

    //     // ── 403 Forbidden ──────────────────────────────────────────────────
    //     if ($httpStatus === 403) {
    //         $this->error('[Google] 403 Forbidden — verifica permissões da conta de serviço e scopes OAuth.');
    //         return null;
    //     }

    //     if ($httpStatus < 200 || $httpStatus >= 300) {
    //         $this->error("[Google] HTTP {$httpStatus} em GET {$url}: " . $body);
    //         return null;
    //     }

    //     return $data ?? [];
    // }

    // /**
    //  * POST request sem autenticação (usado para renovar tokens).
    //  */
    // private function curlPost(string $url, array $payload): ?array
    // {
    //     $ch = curl_init();
    //     curl_setopt_array($ch, [
    //         CURLOPT_URL            => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_TIMEOUT        => 15,
    //         CURLOPT_POST           => true,
    //         CURLOPT_POSTFIELDS     => http_build_query($payload),
    //         CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    //     ]);

    //     $body       = curl_exec($ch);
    //     $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $curlError  = curl_error($ch);
    //     curl_close($ch);

    //     if ($curlError) {
    //         $this->error("[cURL] Erro de rede em POST {$url}: {$curlError}");
    //         return null;
    //     }

    //     if ($httpStatus < 200 || $httpStatus >= 300) {
    //         $this->error("[Google] HTTP {$httpStatus} em POST {$url}: " . $body);
    //         return null;
    //     }

    //     return json_decode($body, true);
    // }

    // // ── Cache ─────────────────────────────────────────────────────────────────

    // private function loadCache(): void
    // {
    //     if (file_exists($this->cacheFile)) {
    //         $this->cache = json_decode(file_get_contents($this->cacheFile), true) ?? [];
    //     }
    // }

    // private function saveCache(): void
    // {
    //     file_put_contents($this->cacheFile, json_encode($this->cache, JSON_PRETTY_PRINT));
    // }
}
