<?php

use App\Services\RepeatedChapterBlockDetector;

it('detects repeated paragraph blocks despite punctuation differences', function () {
    $html = <<<'HTML'
        <p>There was no need for the word ‘beginning’.</p>
        <p>Both fighters had taken their stances.</p>
        <p>The woman raised her shield forward.</p>
        <p>He stepped toward the iron wall.</p>
        <p>The impact threw him backward across the arena.</p>
        <p>There was no need for the word "beginning."</p>
        <p>Both fighters had taken their stances.</p>
        <p>The woman raised her shield forward.</p>
        <p>He stepped toward the iron wall.</p>
        <p>The impact threw him backward across the arena.</p>
        <p>He dodged the next attack.</p>
        HTML;

    $matches = (new RepeatedChapterBlockDetector)->detect($html, 5, 20);

    expect($matches)->toHaveCount(1)
        ->and($matches[0]['first_start'])->toBe(0)
        ->and($matches[0]['first_end'])->toBe(4)
        ->and($matches[0]['second_start'])->toBe(5)
        ->and($matches[0]['second_end'])->toBe(9);
});

it('ignores isolated repeated lines', function () {
    $html = <<<'HTML'
        <p>Bang!</p>
        <p>The first attack landed.</p>
        <p>Bang!</p>
        <p>The second attack landed.</p>
        HTML;

    expect((new RepeatedChapterBlockDetector)->detect($html, 2, 1))->toBeEmpty();
});
