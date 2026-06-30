<?php

function isValidEmail(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone(string $value): bool
{
    return preg_match('/^(?:\+7|7|8)\d{10}$/', $value) === 1;
}

function normalizePhone(string $phone): string
{
    return preg_replace('/\D/', '', $phone);
}