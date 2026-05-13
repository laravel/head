<p align="center">
<a href="https://github.com/laravel/head/actions"><img src="https://github.com/laravel/head/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/dt/laravel/head" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/v/laravel/head" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/head"><img src="https://img.shields.io/packagist/l/laravel/head" alt="License"></a>
</p>

## Introduction

Laravel Head provides first-party head management for Laravel applications, covering metadata, structured data, and performance hints across Blade, Livewire, and Inertia.

## Installation

```bash
composer require laravel/head
```

Laravel Head requires PHP 8.3 or later and supports Laravel 12 and Laravel 13.

## Usage

Register global defaults in a service provider:

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\Head as HeadManager;

Head::defaults(function (HeadManager $head) {
    $head
        ->title('Acme')
        ->description('Build something great.');
});
```

Render the accumulated tags from your Blade layout:

```blade
<head>
    <meta charset="utf-8">
    @head
</head>
```

The full API will be implemented against the project requirements in `PRD.md`.
