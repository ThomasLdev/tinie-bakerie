<?php

namespace App\Enum;

enum PostTranslationSectionType: string
{
    CASE MediaPlain = 'media_plain';

    CASE TextPlain = 'text_plain';

    CASE TextLeftWithMedia = 'text_left_with_media';

    CASE TextRightWithMedia = 'text_right_with_media';
}
