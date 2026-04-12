<?php

namespace App\Services;

use App\Models\JobPosting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobSyncService
{
    private const LOKERBALI_ID = 'lokerbali.id';

    private const LOKERBALI_INFO = 'lokerbali.info';

    public function syncFromLokerBaliId(): int
    {
        $synced = 0;

        $urls = [
            'https://www.lokerbali.id/job-listings/',
            'https://www.lokerbali.id/cari-lowongan/',
            'https://www.lokerbali.id',
        ];

        foreach ($urls as $url) {
            try {
                $response = $this->fetchHtml($url);

                if ($response) {
                    $jobs = $this->parseJobsFromLokerBaliId($response);

                    foreach ($jobs as $jobData) {
                        if ($this->syncJob($jobData)) {
                            $synced++;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Exception syncing lokerbali.id from {$url}: ".$e->getMessage());
            }
        }

        return $synced;
    }

    public function syncFromLokerBaliInfo(): int
    {
        $synced = 0;

        $listingUrls = [
            'https://www.lokerbali.info/cariloker',
            'https://www.lokerbali.info',
        ];

        $companyJobs = [];

        // First pass: collect all job listings from listing pages
        foreach ($listingUrls as $url) {
            try {
                $response = $this->fetchHtml($url);

                if ($response) {
                    $jobs = $this->parseJobsFromLokerBaliInfoList($response);
                    foreach ($jobs as $job) {
                        $key = $job['company_name'].'|'.$job['job_title'];
                        if (! isset($companyJobs[$key])) {
                            $companyJobs[$key] = $job;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Exception syncing lokerbali.info from {$url}: ".$e->getMessage());
            }
        }

        // Second pass: visit each company page to enrich with details
        foreach ($companyJobs as $jobData) {
            try {
                $enriched = $this->enrichFromLokerBaliInfoCompany($jobData);
                if ($this->syncJob($enriched)) {
                    $synced++;
                }
            } catch (\Exception $e) {
                Log::error("Exception enriching job {$jobData['job_title']}: ".$e->getMessage());
            }
        }

        return $synced;
    }

    private function enrichFromLokerBaliInfoCompany(array $jobData): array
    {
        // Fetch the detail page (not the company page) for richer info
        $detailUrl = $jobData['source_url'];
        $response = $this->fetchHtml($detailUrl);

        if (! $response) {
            return $jobData;
        }

        // Extract employment type from detail page
        $jobData['employment_type'] = $this->extractEmploymentType($response);

        // Extract description from detail page
        $jobData['description'] = $this->extractDescription($response);

        // Extract category from page content
        $jobData['category'] = $this->extractCategoryFromDetailPage($response);

        return $jobData;
    }

    private function parseJobsFromLokerBaliId(string $html): array
    {
        $jobs = [];

        preg_match_all('/href="([^"]*job[^"]*)"/', $html, $rawMatches);

        $seenUrls = [];
        foreach ($rawMatches[1] as $rawUrl) {
            $url = str_replace('\/', '/', $rawUrl);

            if (strpos($url, '/job/') === false || strpos($url, '/job-listings') !== false) {
                continue;
            }

            if (isset($seenUrls[$url])) {
                continue;
            }
            $seenUrls[$url] = true;

            preg_match('/\/job\/([^\/]+)\/?$/', $url, $slugMatch);
            $slug = $slugMatch[1] ?? '';
            $title = preg_replace('/-/', ' ', $slug);
            $title = ucwords($title);
            $location = $this->extractLocation($title);
            $company = 'LokerBali';

            if (preg_match('/^(.+?)\s+at\s+(.+)$/', $title, $parts)) {
                $title = trim($parts[1]);
                $company = trim($parts[2]);
            }

            $jobs[] = [
                'external_id' => md5($slug),
                'job_title' => $title,
                'company_name' => $company,
                'location' => $location,
                'source_url' => $url,
                'source_name' => self::LOKERBALI_ID,
                'employment_type' => 'Full Time',
                'description' => null,
                'requirements' => null,
            ];
        }

        return $jobs;
    }

    private function parseJobsFromLokerBaliInfoList(string $html): array
    {
        $jobs = [];

        // lokerbali.info uses /detail/lowongan-{title}-di-{company}-{location}-bali
        // Pattern: <a href="/detail/lowongan-{slug}">... {Job Title}</a>
        $pattern = '#href="(https://www\.lokerbali\.info/detail/lowongan-[^"]+)"[^>]*>.*?<h[34][^>]*>\s*<a[^>]*>\s*([^<]+)\s*</a>#is';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $detailUrl = trim($match[1]);
                $jobTitle = trim(strip_tags($match[2]));

                // Extract company and location from the URL slug
                // e.g. /detail/lowongan-spg-di-pia-bintang-baturiti-kota-denpasar-bali
                if (preg_match('#/detail/lowongan-(?:lowongan-)?(.+?)-di-(.+?)-kota-(.+?)-bali$#', $detailUrl, $urlParts) ||
                    preg_match('#/detail/lowongan-(?:lowongan-)?(.+?)-di-(.+?)-kabupaten-(.+?)-bali$#', $detailUrl, $urlParts)) {
                    $jobTitle = trim(ucwords(str_replace('-', ' ', $urlParts[1])));
                    $company = trim(ucwords(str_replace('-', ' ', $urlParts[2])));
                    $location = trim(ucwords(str_replace('-', ' ', $urlParts[3])));
                } else {
                    // fallback: extract from URL
                    $company = 'LokerBali';
                    $location = 'Bali';
                }

                if (empty($jobTitle)) {
                    continue;
                }

                $jobs[] = [
                    'external_id' => md5($detailUrl),
                    'job_title' => $jobTitle,
                    'company_name' => $company,
                    'location' => $location,
                    'source_url' => $detailUrl,
                    'source_name' => self::LOKERBALI_INFO,
                    'employment_type' => 'Full Time',
                    'description' => null,
                    'requirements' => null,
                ];
            }
        }

        return $jobs;
    }

    private function syncJob(array $jobData): bool
    {
        if (empty($jobData['job_title']) || empty($jobData['source_url'])) {
            return false;
        }

        $exists = JobPosting::where('source_name', $jobData['source_name'])
            ->where('source_url', $jobData['source_url'])
            ->exists();

        if ($exists) {
            return false;
        }

        // Extract category from job title keywords
        $category = $this->extractCategory($jobData['job_title']);

        JobPosting::create([
            'external_id' => $jobData['external_id'] ?? md5($jobData['source_url']),
            'company_name' => $jobData['company_name'] ?? 'LokerBali',
            'job_title' => $jobData['job_title'],
            'location' => $jobData['location'] ?? $this->extractLocation($jobData['job_title']),
            'source_url' => $jobData['source_url'],
            'source_name' => $jobData['source_name'],
            'employment_type' => $jobData['employment_type'] ?? 'Full Time',
            'category' => $category,
            'description' => $jobData['description'] ?? null,
            'requirements' => $jobData['requirements'] ?? null,
            'posted_date' => $jobData['posted_date'] ?? now(),
            'is_approved' => true,
            'is_active' => true,
        ]);

        return true;
    }

    private function extractLocation(string $title): string
    {
        $locations = [
            'Denpasar', 'Badung', 'Gianyar', 'Bangli', 'Buleleng',
            'Tabanan', 'Karangasem', 'Klungkung', 'Jembrana',
            'Ubud', 'Kuta', 'Seminyak', 'Canggu', 'Sanur', 'Nusa Dua',
            'Jimbaran', 'Nusa Penida', 'Legian', 'Kerobokan',
        ];

        foreach ($locations as $location) {
            if (stripos($title, $location) !== false) {
                return $location;
            }
        }

        return 'Bali';
    }

    private function extractCategory(string $title): ?string
    {
        $categories = [
            'Admin' => 'Administrasi / Personalia',
            'Akuntan' => 'Akuntansi / Keuangan',
            'Accounting' => 'Akuntansi / Keuangan',
            'Marketing' => 'Penjualan / Marketing',
            'Sales' => 'Penjualan / Marketing',
            'SPG' => 'Penjualan / Marketing',
            'Frontliner' => 'Pelayanan',
            'Cook' => 'Hospitality / F&B',
            'Chef' => 'Hospitality / F&B',
            'Helper' => 'Pelayanan',
            'Staff' => 'Pelayanan',
            'IT' => 'Komputer / IT',
            'Teknik' => 'Teknik',
            'Guru' => 'Pendidikan / Pelatihan',
            'Teacher' => 'Pendidikan / Pelatihan',
            'Dokter' => 'Kesehatan',
            'Nurse' => 'Kesehatan',
            'Perawat' => 'Kesehatan',
            'Konstruksi' => 'Bangunan / Konstruksi',
            'Design' => 'Seni / Media / Komunikasi',
            'Designer' => 'Seni / Media / Komunikasi',
        ];

        foreach ($categories as $keyword => $category) {
            if (stripos($title, $keyword) !== false) {
                return $category;
            }
        }

        return null;
    }

    private function extractEmploymentType(string $html): string
    {
        $htmlLower = strtolower($html);

        if (strpos($htmlLower, 'part time') !== false || strpos($htmlLower, 'part-time') !== false) {
            return 'Part Time';
        }
        if (strpos($htmlLower, 'freelance') !== false) {
            return 'Freelance';
        }
        if (strpos($htmlLower, 'remote') !== false || strpos($htmlLower, 'kerja jarak jauh') !== false) {
            return 'Remote';
        }
        if (strpos($htmlLower, 'internship') !== false || strpos($htmlLower, 'magang') !== false) {
            return 'Internship';
        }
        if (strpos($htmlLower, 'daily worker') !== false || strpos($htmlLower, 'harian') !== false) {
            return 'Daily Worker';
        }

        return 'Full Time';
    }

    private function extractDescription(string $html): ?string
    {
        // Try to find description section
        $patterns = [
            '/<h[34][^>]*>Deskripsi.*?<\/h[34]>(.*?)(?=<h[34]|$)/is',
            '/<h[34][^>]*>Kualifikasi.*?<\/h[34]>(.*?)(?=<h[34]|$)/is',
            '/<div[^>]*class="[^"]*deskripsi[^"]*"[^>]*>(.*?)<\/div>/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match)) {
                $text = strip_tags($match[1]);
                $text = html_entity_decode($text);
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);

                if (strlen($text) > 20) {
                    return $text;
                }
            }
        }

        return null;
    }

    private function extractCategoryFromDetailPage(string $html): ?string
    {
        // Look for category link in detail pages
        if (preg_match('#href="[^"]*cari/profesi/[^"]+"[^>]*>([^<]+)</a>#', $html, $match)) {
            $category = trim($match[1]);
            if (! empty($category)) {
                return $category;
            }
        }

        // Fallback: use the category menu links
        $categoryKeywords = [
            'administrasi' => 'Administrasi / Personalia',
            'akuntansi' => 'Akuntansi / Keuangan',
            'keuangan' => 'Akuntansi / Keuangan',
            'bangunan' => 'Bangunan / Konstruksi',
            'konstruksi' => 'Bangunan / Konstruksi',
            'hospitality' => 'Hospitality / F&B',
            'makanan' => 'Hospitality / F&B',
            'kesehatan' => 'Kesehatan',
            'komputer' => 'Komputer / IT',
            'it' => 'Komputer / IT',
            'manufaktur' => 'Manufaktur',
            'pelayanan' => 'Pelayanan',
            'pendidikan' => 'Pendidikan / Pelatihan',
            'pelatihan' => 'Pendidikan / Pelatihan',
            'penjualan' => 'Penjualan / Marketing',
            'marketing' => 'Penjualan / Marketing',
            'seni' => 'Seni / Media / Komunikasi',
            'media' => 'Seni / Media / Komunikasi',
            'komunikasi' => 'Seni / Media / Komunikasi',
            'teknik' => 'Teknik',
        ];

        $htmlLower = strtolower($html);
        foreach ($categoryKeywords as $keyword => $category) {
            if (strpos($htmlLower, $keyword) !== false) {
                return $category;
            }
        }

        return null;
    }

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get($url);

            if ($response->successful()) {
                return $this->normalizeHtml($response->body());
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch {$url}: ".$e->getMessage());
        }

        return null;
    }

    private function normalizeHtml(string $html): string
    {
        // Remove excessive whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        // Decode HTML entities
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove script and style blocks
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        // Remove HTML comments
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        return $html;
    }

    public function syncAll(): int
    {
        return $this->syncFromLokerBaliId() + $this->syncFromLokerBaliInfo();
    }
}
