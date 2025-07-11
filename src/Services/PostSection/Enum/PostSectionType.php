<?php

namespace App\Services\PostSection\Enum;

enum PostSectionType: string
{
    case Default = 'default';

    case TwoColumns = 'two_columns';

    case TwoColumnsMediaLeft = 'two_columns_media_left';
}
