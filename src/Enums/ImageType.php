<?php

declare(strict_types=1);

namespace Laravel\Head\Enums;

enum ImageType: string
{
    case Avif = 'image/avif';
    case Gif = 'image/gif';
    case Ico = 'image/x-icon';
    case Jpeg = 'image/jpeg';
    case Png = 'image/png';
    case Svg = 'image/svg+xml';
    case Webp = 'image/webp';
}
