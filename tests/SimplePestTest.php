<?php

test('pest is working', function () {
    expect(true)->toBeTrue();
});

test('basic math', function () {
    expect(1 + 1)->toBe(2);
});

test('string test', function () {
    expect('hello')->toBeString();
});