<?php

namespace App\Services;

/**
 * Balinese (Saka) Calendar Service
 *
 * Calculates Pawukon (210-day cycle) and Saka calendar values using JDN arithmetic.
 *
 * Key epochs (JDN):
 * - Pawukon: July 5, 2020 = JDN 2459036 = Day 1, Wuku 1 (Sinta), Redite (Sunday)
 *   Source: Wikipedia Pawukon calendar (https://en.wikipedia.org/wiki/Pawukon_calendar)
 * - Saka epoch JDN 1740714 (March 22, 78 AD) is used for sasih-style month counting.
 *   Penanggal (1–30) follows a synodic model: lunation age from a reference new-moon Julian
 *   day is mapped into 30 steps (~29.53 civil days per full cycle). The reference JD is
 *   tuned so April 2026 matches common Balinese tables (Purnama 2 Apr, Tilem 16 Apr).
 *
 * Verification:
 * - July 5, 2020 = Day 1 = Paing, Redite (matches Wikipedia table day 1)
 * - January 5, 2021 = Day 185 = Umanis, Anggara (Wikipedia “185th day” example)
 * - April 12, 2026 = Day 8 = Wage, Redite (Gregorian Sunday)
 */
class BalineseCalendarService
{
    // ============================================================
    // WUKU (30 wuku / 210-day cycle)
    // ============================================================
    public const WUKU_NAMES = [
        1 => 'Sinta', 2 => 'Landep', 3 => 'Ukir', 4 => 'Kulantir', 5 => 'Tolu',
        6 => 'Gumbreg', 7 => 'Wariga', 8 => 'Warigadean', 9 => 'Julungwangi', 10 => 'Sungsang',
        11 => 'Dungulan', 12 => 'Kuningan', 13 => 'Langkir', 14 => 'Medangsya', 15 => 'Pujut',
        16 => 'Pahang', 17 => 'Kerulut', 18 => 'Merakih', 19 => 'Tambir', 20 => 'Medangkungan',
        21 => 'Matal', 22 => 'Uye', 23 => 'Menahil', 24 => 'Perangbakat', 25 => 'Bala',
        26 => 'Ugu', 27 => 'Wayang', 28 => 'Kelawu', 29 => 'Dukut', 30 => 'Watugunung',
    ];

    public const WUKU_URIP = [
        1 => 7, 2 => 1, 3 => 4, 4 => 6, 5 => 5,
        6 => 8, 7 => 9, 8 => 3, 9 => 7, 10 => 1,
        11 => 4, 12 => 6, 13 => 5, 14 => 8, 15 => 9,
        16 => 3, 17 => 7, 18 => 1, 19 => 4, 20 => 6,
        21 => 5, 22 => 8, 23 => 9, 24 => 3, 25 => 7,
        26 => 1, 27 => 4, 28 => 6, 29 => 5, 30 => 8,
    ];

    // Pawukon epoch: July 5, 2020 = JDN 2459036 (= PHP gregoriantojd), Day 1 per Wikipedia
    private const PAWUKON_EPOCH_JDN = 2459036;

    // ============================================================
    // PANCAWARA (5-day cycle)
    // ============================================================
    // Wikipedia table order: Paing(1), Pon(2), Wage(3), Keliwon(4), Umanis(5)
    public const PANCAWARA_NAMES = [1 => 'Paing', 2 => 'Pon', 3 => 'Wage', 4 => 'Keliwon', 5 => 'Umanis'];

    public const PANCAWARA_URIP = [1 => 5, 2 => 9, 3 => 7, 4 => 4, 5 => 8];

    // ============================================================
    // SAPTAWARA (7-day week, 0=Redite=Sunday)
    // ============================================================
    public const SAPTAWARA_NAMES = [
        0 => 'Redite',    // Sunday
        1 => 'Coma',      // Monday
        2 => 'Anggara',   // Tuesday
        3 => 'Buddha',    // Wednesday
        4 => 'Wraspati',  // Thursday
        5 => 'Sukra',    // Friday
        6 => 'Saniscara', // Saturday
    ];

    public const SAPTAWARA_URIP = [0 => 5, 1 => 4, 2 => 3, 3 => 7, 4 => 8, 5 => 6, 6 => 9];

    // ============================================================
    // SASIH (Balinese months)
    // ============================================================
    public const SASIH_NAMES = [
        1 => ['Kasa', 'Srawana'],
        2 => ['Karo', 'Bhadrapada'],
        3 => ['Katiga', 'Aswina'],
        4 => ['Kapat', 'Kartika'],
        5 => ['Kalima', 'Margasira'],
        6 => ['Kanem', 'Pausya'],
        7 => ['Kapitu', 'Magha'],
        8 => ['Kawolu', 'Phalguna'],
        9 => ['Kasanga', 'Caitra'],
        10 => ['Kadasa', 'Waisakha'],
        11 => ['Destha', 'Jyestha'],
        12 => ['Sadha', 'Asadha'],
    ];

    public const EKAWARA_NAMES = [1 => 'Luang'];

    public const EKAWARA_URIP = [1 => 1];

    public const DWIWARA_NAMES = [1 => 'Menga', 2 => 'Pepet'];

    public const DWIWARA_URIP = [1 => 5, 2 => 7];

    public const TRIWARA_NAMES = [1 => 'Dora', 2 => 'Wahya', 3 => 'Byantara'];

    public const TRIWARA_URIP = [1 => 9, 2 => 4, 3 => 7];

    public const CATURWARA_NAMES = [1 => 'Sri', 2 => 'Laba', 3 => 'Jaya', 4 => 'Mandala'];

    public const CATURWARA_URIP = [1 => 4, 2 => 5, 3 => 9, 4 => 7];

    public const SADWARA_NAMES = [1 => 'Tungleh', 2 => 'Aryang', 3 => 'Wurukung', 4 => 'Paniron', 5 => 'Was', 6 => 'Maulu'];

    public const SADWARA_URIP = [1 => 7, 2 => 6, 3 => 5, 4 => 8, 5 => 9, 6 => 3];

    public const ASTAWARA_NAMES = [1 => 'Sri', 2 => 'Indra', 3 => 'Guru', 4 => 'Yama', 5 => 'Ludra', 6 => 'Brahma', 7 => 'Kala', 8 => 'Uma'];

    public const ASTAWARA_URIP = [1 => 6, 2 => 5, 3 => 8, 4 => 9, 5 => 3, 6 => 7, 7 => 1, 8 => 4];

    public const SANGGAWARA_NAMES = [1 => 'Dangu', 2 => 'Jagur', 3 => 'Gigis', 4 => 'Nohan', 5 => 'Ogan', 6 => 'Erangan', 7 => 'Urungan', 8 => 'Tulus', 9 => 'Dadi'];

    public const SANGGAWARA_URIP = [1 => 9, 2 => 8, 3 => 6, 4 => 7, 5 => 4, 6 => 5, 7 => 7, 8 => 3, 9 => 4];

    public const DASASAWARA_NAMES = [1 => 'Pandita', 2 => 'Pati', 3 => 'Suka', 4 => 'Duka', 5 => 'Sri', 6 => 'Manu', 7 => 'Manusa', 8 => 'Raja', 9 => 'Dewa', 10 => 'Raksasa'];

    public const DASASAWARA_URIP = [1 => 5, 2 => 7, 3 => 10, 4 => 4, 5 => 6, 6 => 2, 7 => 3, 8 => 8, 9 => 9, 10 => 1];

    public const INGKEL_NAMES = [1 => 'Wong', 2 => 'Sato', 3 => 'Mina', 4 => 'Manuk', 5 => 'Taru', 6 => 'Buku'];

    public const JEJEPAN_NAMES = [1 => 'Mina', 2 => 'Taru', 3 => 'Sato', 4 => 'Patra', 5 => 'Wong', 6 => 'Paksi'];

    public const WATEK_ALIT_NAMES = [1 => 'Uler', 2 => 'Gajah', 3 => 'Lembu', 4 => 'Lintah'];

    public const WATEK_MADYA_NAMES = [1 => 'Gajah', 2 => 'Watu', 3 => 'Buta', 4 => 'Suku', 5 => 'Wong'];

    public const EKA_JALA_RSI_NAMES = [
        1 => 'Bagna mapasah', 2 => 'Bahu putra', 3 => 'Buat astawa', 4 => 'Buat lara',
        5 => 'Buat merang', 6 => 'Buat sebet', 7 => 'Buat kingking', 8 => 'Buat suka',
        9 => 'Dahat kingking', 10 => 'Kamaranan', 11 => 'Kamretaan', 12 => 'Kasobagian',
        13 => 'Kinasihan amreta', 14 => 'Kinasihan jana', 15 => 'Langgeng kayohanaan',
        16 => 'Lewih bagia', 17 => 'Manggang bagia', 18 => 'Manggang suka', 19 => 'Patining amreta',
        20 => 'Rahayu', 21 => 'Sidha kasobagian', 22 => 'Subagia', 23 => 'Suka kapiggins',
        24 => 'Suka piniggins', 25 => 'Suka rahayu', 26 => 'Tininggaling suka',
        27 => 'Wredhi putra', 28 => 'Wredhi sarwa mule',
    ];

    public const LINTANG_NAMES = [
        1 => 'Gajah', 2 => 'Kiriman', 3 => 'Jong Sarat', 4 => 'Atiwa-tiwa',
        5 => 'Sangka Tikel', 6 => 'Bubu Bolong', 7 => 'Sugenge', 8 => 'Uluku',
        9 => 'Pedati', 10 => 'Kuda', 11 => 'Gajah Mina', 12 => 'Bade',
        13 => 'Magelut', 14 => 'Pagelangan', 15 => 'Kala Sungsang', 16 => 'Kukus',
        17 => 'Asu', 18 => 'Kartika', 19 => 'Naga', 20 => 'Banak Angerem',
        21 => 'Hru Panah', 22 => 'Patrem', 23 => 'Lembu', 24 => 'Depat Sidamalung',
        25 => 'Tangis', 26 => 'Salah Ukur', 27 => 'Perahu Pegat', 28 => 'Puwuh Atarung',
        29 => 'Lawean Goang', 30 => 'Kelapa', 31 => 'Yuyu', 32 => 'Lumbung',
        33 => 'Kumbha', 34 => 'Udang', 35 => 'Begoong',
    ];

    public const PARARASAN_NAMES = [
        1 => 'Laku bumi', 2 => 'Laku api', 3 => 'Laku angin', 4 => 'Laku pandita sakti',
        5 => 'Aras tuding', 6 => 'Aras kembang', 7 => 'Laku bintang', 8 => 'Laku bulan',
        9 => 'Laku surya', 10 => 'Laku air', 11 => 'Laku pretiwi', 12 => 'Laku agni agung',
    ];

    public const PANCA_SUDHA_NAMES = [
        1 => 'Wisesa segara', 2 => 'Tunggak semi', 3 => 'Satria wibhawa',
        4 => 'Sumur sinaba', 5 => 'Bumi kapetak', 6 => 'Satria wirang', 7 => 'Lebu katiup angin',
    ];

    public const ZODIAK_NAMES = [
        1 => 'Aries', 2 => 'Taurus', 3 => 'Gemini', 4 => 'Cancer',
        5 => 'Leo', 6 => 'Virgo', 7 => 'Libra', 8 => 'Scorpio',
        9 => 'Sagitarius', 10 => 'Capricorn', 11 => 'Aquarius', 12 => 'Pisces',
    ];

    // Saka epoch: March 22, 78 AD = JDN 1740714
    private const SAKA_EPOCH_JDN = 1740714;

    /** @var \DateTimeImmutable */
    private $date;

    public function __construct(?\DateTimeInterface $date = null)
    {
        $this->date = $date
            ? \DateTimeImmutable::createFromInterface($date)
            : new \DateTimeImmutable('today');
    }

    public static function forDate(int $year, int $month, int $day): self
    {
        return new self(new \DateTimeImmutable("{$year}-{$month}-{$day}"));
    }

    public static function today(): self
    {
        return new self(new \DateTimeImmutable('today'));
    }

    // ============================================================
    // JULIAN DAY NUMBER — standard integer-arithmetic formula
    // ============================================================
    public function getJdn(): int
    {
        $y = (int) $this->date->format('Y');
        $m = (int) $this->date->format('n');
        $d = (int) $this->date->format('j');

        $a = (int) ((14 - $m) / 12);
        $yAdj = $y + 4800 - $a;
        $mAdj = $m + 12 * $a - 3;

        return $d
            + (int) ((153 * $mAdj + 2) / 5)
            + 365 * $yAdj
            + (int) ($yAdj / 4)
            - (int) ($yAdj / 100)
            + (int) ($yAdj / 400)
            - 32045;
    }

    private static function jdnToDate(int $jdn): \DateTimeImmutable
    {
        $a = $jdn + 32044;
        $b = (int) ((4 * $a + 3) / 146097);
        $c = $a - (int) ((146097 * $b) / 4);
        $d = (int) ((4 * $c + 3) / 1461);
        $e = $c - (int) ((1461 * $d) / 4);
        $m = (int) ((5 * $e + 2) / 153);

        $day = $e - (int) ((153 * $m + 2) / 5) + 1;
        $month = $m + 3 - 12 * (int) (($m) / 10);
        $year = 100 * $b + $d - 4800 + (int) (($m) / 10);

        return new \DateTimeImmutable("{$year}-{$month}-{$day}");
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function posMod(int $a, int $m): int
    {
        $r = $a % $m;

        return ($r >= 0) ? $r : $r + $m;
    }

    // ============================================================
    // PAWUKON
    // Day 1..210 from JDN epoch; Wuku = ceil(day / 7). Pancawara / Saptawara cycle
    // with day 1 = Paing + Redite per Wikipedia table (same as Gregorian weekday).
    // ============================================================

    /**
     * Position in the 210-day Pawukon cycle (1 = first day of the cycle anchored at the epoch).
     */
    public function getPawukonDayInCycle(): int
    {
        return $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN, 210) + 1;
    }

    public function getWukuNumber(): int
    {
        $dayNum = $this->getPawukonDayInCycle();

        return (int) ceil($dayNum / 7.0);
    }

    public function getWukuName(): string
    {
        return self::WUKU_NAMES[$this->getWukuNumber()] ?? 'Unknown';
    }

    public function getWukuUrip(): int
    {
        return self::WUKU_URIP[$this->getWukuNumber()] ?? 0;
    }

    public function getWukuDetails(): array
    {
        $no = $this->getWukuNumber();

        return [
            'no' => $no,
            'name' => self::WUKU_NAMES[$no] ?? 'Unknown',
            'urip' => self::WUKU_URIP[$no] ?? 0,
        ];
    }

    // ============================================================
    // SAPTAWARA — same order as Wikipedia Saptawara; 0=Redite on cycle day 1
    // ============================================================
    public function getSaptawara(): array
    {
        $no = $this->posMod($this->getPawukonDayInCycle() - 1, 7);

        return [
            'no' => $no,
            'name' => self::SAPTAWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::SAPTAWARA_URIP[$no] ?? 0,
        ];
    }

    // ============================================================
    // PANCAWARA — Wikipedia order: Paing, Pon, Wage, Keliwon, Umanis (5-day cycle)
    // ============================================================
    public function getPancawara(): array
    {
        $no = $this->posMod($this->getPawukonDayInCycle() - 1, 5) + 1;

        return [
            'no' => $no,
            'name' => self::PANCAWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::PANCAWARA_URIP[$no] ?? 0,
        ];
    }

    public function getEkawara(): array
    {
        return ['no' => 1, 'name' => self::EKAWARA_NAMES[1], 'urip' => self::EKAWARA_URIP[1]];
    }

    public function getDwiwara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 2, 2) + 1;

        return [
            'no' => $no,
            'name' => self::DWIWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::DWIWARA_URIP[$no] ?? 0,
        ];
    }

    public function getTriwara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 3, 3) + 1;

        return [
            'no' => $no,
            'name' => self::TRIWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::TRIWARA_URIP[$no] ?? 0,
        ];
    }

    public function getCaturwara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 4, 4) + 1;

        return [
            'no' => $no,
            'name' => self::CATURWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::CATURWARA_URIP[$no] ?? 0,
        ];
    }

    public function getSadwara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 5, 6) + 1;

        return [
            'no' => $no,
            'name' => self::SADWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::SADWARA_URIP[$no] ?? 0,
        ];
    }

    public function getAstawara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 6, 8) + 1;

        return [
            'no' => $no,
            'name' => self::ASTAWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::ASTAWARA_URIP[$no] ?? 0,
        ];
    }

    public function getSangkawara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 7, 9) + 1;

        return [
            'no' => $no,
            'name' => self::SANGGAWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::SANGGAWARA_URIP[$no] ?? 0,
        ];
    }

    public function getDasawara(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 8, 10) + 1;

        return [
            'no' => $no,
            'name' => self::DASASAWARA_NAMES[$no] ?? 'Unknown',
            'urip' => self::DASASAWARA_URIP[$no] ?? 0,
        ];
    }

    // ============================================================
    // PENANGGAL / PANGELONG (30 steps over ~29.53-day synodic month)
    // Lunation age = JDN − last new moon (JDN), where "last new moon" steps by SYNODIC_DAYS
    // from REFERENCE_NEW_MOON_JD. Penanggal = floor((age / synodic) * 30) + 1 (clamped 1–30).
    // REFERENCE_NEW_MOON_JD is calibrated to published 2026-04 Purnama/Tilem dates; revisit
    // if almanac sources disagree for other years.
    // ============================================================
    private const SYNODIC_DAYS = 29.530588853;

    /** Julian day number at the start of the civil date (same basis as {@see getJdn()}). */
    private const PENANGGAL_REFERENCE_NEW_MOON_JD = 2461118.45;

    /**
     * Age in days since the last new moon for this calendar day's JDN.
     */
    private function getLunationAgeDays(float $jd): float
    {
        $syn = self::SYNODIC_DAYS;
        $ref = self::PENANGGAL_REFERENCE_NEW_MOON_JD;
        $k = (int) floor(($jd - $ref) / $syn);

        return $jd - ($ref + ($k * $syn));
    }

    public function getPenanggal(): int
    {
        $jd = (float) $this->getJdn();
        $age = $this->getLunationAgeDays($jd);
        $position = ($age / self::SYNODIC_DAYS) * 30.0;
        $pen = (int) floor($position) + 1;

        return max(1, min(30, $pen));
    }

    public function getPenanggalName(): string
    {
        $p = $this->getPenanggal();

        return $p <= 15
            ? $p.' (Penanggal)'
            : ($p - 15).' (Pangelong)';
    }

    public function isPangelong(): bool
    {
        return $this->getPenanggal() > 15;
    }

    public function isPenanggal(): bool
    {
        return $this->getPenanggal() <= 15;
    }

    public function isPurnama(): bool
    {
        return $this->getPenanggal() === 15;
    }

    public function isTilem(): bool
    {
        return $this->getPenanggal() === 30;
    }

    public function isNgunaratri(): bool
    {
        return $this->getPenanggal() === 6;
    }

    // ============================================================
    // SASIH (lunar month)
    // ============================================================
    public function getSasih(): int
    {
        $diff = $this->getJdn() - self::SAKA_EPOCH_JDN;
        if ($diff < 0) {
            return 0;
        }

        $approxMonths = (int) ($diff / 29.53);

        return ($approxMonths % 12) + 1;
    }

    public function getSasihName(): string
    {
        $s = $this->getSasih();

        if ($s >= 1 && $s <= 12) {
            return self::SASIH_NAMES[$s][0].' / '.self::SASIH_NAMES[$s][1];
        }

        return 'Nampih '.($s % 12);
    }

    public function isNampihSasih(): bool
    {
        $diff = $this->getJdn() - self::SAKA_EPOCH_JDN;
        $approxMonths = (int) ($diff / 29.53);

        return $approxMonths >= 12;
    }

    // ============================================================
    // SAKA YEAR — Gregorian year - 78 (approximate, astronomical)
    // ============================================================
    public function getSakaYear(): int
    {
        $gYear = (int) $this->date->format('Y');
        $gMonth = (int) $this->date->format('n');
        $gDay = (int) $this->date->format('j');

        // Saka year starts around March/April (near spring equinox)
        // If before ~March 21, we're still in previous Saka year
        if ($gMonth < 3 || ($gMonth === 3 && $gDay < 21)) {
            return $gYear - 79;
        }

        return $gYear - 78;
    }

    public function getSakaYearName(): string
    {
        return 'Saka '.$this->getSakaYear();
    }

    // ============================================================
    // SUPPORTING CYCLES
    // ============================================================
    public function getIngkel(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 4, 6) + 1;

        return ['no' => $no, 'name' => self::INGKEL_NAMES[$no] ?? 'Unknown'];
    }

    public function getJejepan(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 5, 6) + 1;

        return ['no' => $no, 'name' => self::JEJEPAN_NAMES[$no] ?? 'Unknown'];
    }

    public function getWatekAlit(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 4, 4) + 1;

        return ['no' => $no, 'name' => self::WATEK_ALIT_NAMES[$no] ?? 'Unknown'];
    }

    public function getWatekMadya(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 5, 5) + 1;

        return ['no' => $no, 'name' => self::WATEK_MADYA_NAMES[$no] ?? 'Unknown'];
    }

    public function getEkaJalaRsi(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 3, 28) + 1;

        return ['no' => $no, 'name' => self::EKA_JALA_RSI_NAMES[$no] ?? 'Unknown'];
    }

    public function getLintang(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 2, 35) + 1;

        return ['no' => $no, 'name' => self::LINTANG_NAMES[$no] ?? 'Unknown'];
    }

    public function getPararasan(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 3, 12) + 1;

        return ['no' => $no, 'name' => self::PARARASAN_NAMES[$no] ?? 'Unknown'];
    }

    public function getPancaSudha(): array
    {
        $no = $this->posMod($this->getJdn() - self::PAWUKON_EPOCH_JDN + 4, 7) + 1;

        return ['no' => $no, 'name' => self::PANCA_SUDHA_NAMES[$no] ?? 'Unknown'];
    }

    /**
     * Tropical (Gregorian) zodiac, matching {@see self::ZODIAK_NAMES} indices 1–12.
     */
    public function getZodiak(): array
    {
        $month = (int) $this->date->format('n');
        $day = (int) $this->date->format('j');

        $no = match (true) {
            ($month === 3 && $day >= 21) || ($month === 4 && $day <= 19) => 1,
            ($month === 4 && $day >= 20) || ($month === 5 && $day <= 20) => 2,
            ($month === 5 && $day >= 21) || ($month === 6 && $day <= 20) => 3,
            ($month === 6 && $day >= 21) || ($month === 7 && $day <= 22) => 4,
            ($month === 7 && $day >= 23) || ($month === 8 && $day <= 22) => 5,
            ($month === 8 && $day >= 23) || ($month === 9 && $day <= 22) => 6,
            ($month === 9 && $day >= 23) || ($month === 10 && $day <= 22) => 7,
            ($month === 10 && $day >= 23) || ($month === 11 && $day <= 21) => 8,
            ($month === 11 && $day >= 22) || ($month === 12 && $day <= 21) => 9,
            ($month === 12 && $day >= 22) || ($month === 1 && $day <= 19) => 10,
            ($month === 1 && $day >= 20) || ($month === 2 && $day <= 18) => 11,
            default => 12,
        };

        return ['no' => $no, 'name' => self::ZODIAK_NAMES[$no] ?? 'Unknown'];
    }

    // ============================================================
    // FULL INFO
    // ============================================================
    public function getFullInfo(): array
    {
        return [
            'gregorian_date' => [
                'year' => (int) $this->date->format('Y'),
                'month' => (int) $this->date->format('n'),
                'day' => (int) $this->date->format('j'),
                'formatted' => $this->date->format('l, j F Y'),
            ],
            'saka' => [
                'year' => $this->getSakaYear(),
                'year_name' => $this->getSakaYearName(),
                'sasih' => $this->getSasih(),
                'sasih_name' => $this->getSasihName(),
                'is_nampih' => $this->isNampihSasih(),
            ],
            'penanggal' => [
                'number' => $this->getPenanggal(),
                'name' => $this->getPenanggalName(),
                'is_penanggal' => $this->isPenanggal(),
                'is_pangelong' => $this->isPangelong(),
                'is_purnama' => $this->isPurnama(),
                'is_tilem' => $this->isTilem(),
                'is_ngunaratri' => $this->isNgunaratri(),
            ],
            'pawukon' => [
                'wuku' => $this->getWukuNumber(),
                'wuku_name' => $this->getWukuName(),
                'wuku_urip' => $this->getWukuUrip(),
            ],
            'wewaran' => [
                'pancawara' => $this->getPancawara(),
                'saptawara' => $this->getSaptawara(),
                'ekawara' => $this->getEkawara(),
                'dwiwara' => $this->getDwiwara(),
                'triwara' => $this->getTriwara(),
                'caturwara' => $this->getCaturwara(),
                'sadwara' => $this->getSadwara(),
                'astawara' => $this->getAstawara(),
                'sangkawara' => $this->getSangkawara(),
                'dasawara' => $this->getDasawara(),
            ],
            'supporting' => [
                'ingkel' => $this->getIngkel(),
                'jejepan' => $this->getJejepan(),
                'watek_alit' => $this->getWatekAlit(),
                'watek_madya' => $this->getWatekMadya(),
                'eka_jala_rsi' => $this->getEkaJalaRsi(),
                'lintang' => $this->getLintang(),
                'pararasan' => $this->getPararasan(),
                'panca_sudha' => $this->getPancaSudha(),
                'zodiak' => $this->getZodiak(),
            ],
        ];
    }

    // ============================================================
    // MONTH GRID
    // ============================================================
    public function getMonthGrid(int $year, int $month): array
    {
        $firstOfMonth = new \DateTimeImmutable("{$year}-{$month}-01");
        $lastOfMonth = $firstOfMonth->modify('last day of this month');
        $daysInMonth = (int) $lastOfMonth->format('j');

        $calFirst = new self($firstOfMonth);
        $jdnFirst = $calFirst->getJdn();
        $startDayOfWeek = $this->posMod($calFirst->getPawukonDayInCycle() - 1, 7);

        $grid = [];

        for ($week = 0; $week < 6; $week++) {
            for ($dow = 0; $dow < 7; $dow++) {
                $dayIdx = $week * 7 + $dow;
                $cellJdn = $jdnFirst - $startDayOfWeek + $dayIdx;
                $dayNum = $this->posMod($cellJdn - self::PAWUKON_EPOCH_JDN, 210) + 1;
                $wukuNo = (int) ceil($dayNum / 7.0);
                $cal = new self(self::jdnToDate($cellJdn));
                $dayOfMonth = $dayIdx - $startDayOfWeek + 1;

                if ($dayOfMonth >= 1 && $dayOfMonth <= $daysInMonth) {
                    $grid[$week][$dow] = [
                        'wuku' => $wukuNo,
                        'wuku_name' => self::WUKU_NAMES[$wukuNo] ?? '?',
                        'day' => $dayOfMonth,
                        'is_current_month' => true,
                        'penanggal' => $cal->getPenanggal(),
                        'is_purnama' => $cal->isPurnama(),
                        'is_tilem' => $cal->isTilem(),
                        'saptawara' => $cal->getSaptawara(),
                        'pancawara' => $cal->getPancawara(),
                    ];
                } else {
                    $grid[$week][$dow] = null;
                }
            }
        }

        return $grid;
    }

    // ============================================================
    // STATIC HELPERS
    // ============================================================
    public static function getAllWuku(): array
    {
        $wuku = [];
        for ($i = 1; $i <= 30; $i++) {
            $wuku[$i] = [
                'no' => $i,
                'name' => self::WUKU_NAMES[$i],
                'urip' => self::WUKU_URIP[$i],
            ];
        }

        return $wuku;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
}
