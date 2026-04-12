<?php

use App\Services\BalineseCalendarService;

test('jul 5 2020 is pawukon day 1 wuku sinta paing redite', function () {
    $cal = BalineseCalendarService::forDate(2020, 7, 5);
    expect($cal->getPawukonDayInCycle())->toBe(1)
        ->and($cal->getWukuNumber())->toBe(1)
        ->and($cal->getWukuName())->toBe('Sinta')
        ->and($cal->getPancawara()['name'])->toBe('Paing')
        ->and($cal->getSaptawara()['name'])->toBe('Redite');
});

test('jan 5 2021 is day 185 umanis anggara per wikipedia', function () {
    $cal = BalineseCalendarService::forDate(2021, 1, 5);
    expect($cal->getPawukonDayInCycle())->toBe(185)
        ->and($cal->getPancawara()['name'])->toBe('Umanis')
        ->and($cal->getSaptawara()['name'])->toBe('Anggara');
});

test('apr 12 2026 is sunday redite wage landep', function () {
    $cal = BalineseCalendarService::forDate(2026, 4, 12);
    $dow = (int) (new DateTimeImmutable('2026-04-12'))->format('w');
    expect($dow)->toBe(0)
        ->and($cal->getPawukonDayInCycle())->toBe(8)
        ->and($cal->getWukuNumber())->toBe(2)
        ->and($cal->getWukuName())->toBe('Landep')
        ->and($cal->getSaptawara()['name'])->toBe('Redite')
        ->and($cal->getPancawara()['name'])->toBe('Wage');
});

test('april 2026 purnama and tilem match common balinese almanac dates', function () {
    $purnama = BalineseCalendarService::forDate(2026, 4, 2);
    expect($purnama->getPenanggal())->toBe(15)
        ->and($purnama->isPurnama())->toBeTrue();

    $tilem = BalineseCalendarService::forDate(2026, 4, 16);
    expect($tilem->getPenanggal())->toBe(30)
        ->and($tilem->isTilem())->toBeTrue();
});

test('tropical zodiac matches common boundaries', function () {
    expect(BalineseCalendarService::forDate(2026, 4, 12)->getZodiak()['name'])->toBe('Aries')
        ->and(BalineseCalendarService::forDate(2026, 4, 20)->getZodiak()['name'])->toBe('Taurus')
        ->and(BalineseCalendarService::forDate(2026, 1, 15)->getZodiak()['name'])->toBe('Capricorn')
        ->and(BalineseCalendarService::forDate(2026, 1, 25)->getZodiak()['name'])->toBe('Aquarius')
        ->and(BalineseCalendarService::forDate(2026, 12, 25)->getZodiak()['name'])->toBe('Capricorn');
});

test('month grid april 2026 includes day 12 with correct wewaran', function () {
    $grid = (new BalineseCalendarService(new DateTimeImmutable('2026-04-01')))->getMonthGrid(2026, 4);
    $cell12 = null;
    foreach ($grid as $week) {
        foreach ($week as $cell) {
            if ($cell !== null && ($cell['day'] ?? 0) === 12) {
                $cell12 = $cell;
                break 2;
            }
        }
    }
    expect($cell12)->not->toBeNull()
        ->and($cell12['wuku_name'])->toBe('Landep')
        ->and($cell12['saptawara']['name'])->toBe('Redite')
        ->and($cell12['pancawara']['name'])->toBe('Wage');
});
