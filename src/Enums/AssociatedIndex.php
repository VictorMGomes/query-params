<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Enums;

use ReflectionClass;

final class AssociatedIndex
{
    public const TYPES = 'types';

    public const TYPE = 'type';

    public const RULES = 'rules';

    public const COLUMNS = 'columns';

    public const NAME = 'name';

    public const FILTERS = 'filters';

    public const FILTER = 'filter';

    public const SORTS = 'sorts';

    public const SORT = 'sort';

    public const PAGES = 'pages';

    public const PAGE = 'page';

    public const FIELDS = 'fields';

    public const FIELD = 'field';

    public const INCLUDES = 'includes';

    public const INCLUDE = 'include';

    public const RELATIONS = 'relations';

    public const RELATION = 'relation';

    public const NUMBER = 'number';

    public const LIMIT = 'limit';

    public const COLLECTION = 'collection';

    public const PER_PAGE = 'per_page';

    public const CURRENT_PAGE = 'current_page';

    public const FROM = 'from';

    public const TO = 'to';

    public const LAST_PAGE = 'last_page';

    public const PREV_PAGE_URL = 'prev_page_url';

    public const NEXT_PAGE_URL = 'next_page_url';

    public const FIRST_PAGE_URL = 'first_page_url';

    public const LAST_PAGE_URL = 'last_page_url';

    public const TOTAL = 'total';

    public const PATH = 'path';

    public const LINKS = 'links';

    public const URL = 'url';

    public const LABEL = 'label';

    public const ACTIVE = 'active';

    public const PREVIOUS_LABEL = '&laquo; Previous';

    public const NEXT_LABEL = 'Next &raquo;';

    public const TABLES = 'tables';

    public const TABLE = 'table';

    public const OFFSET = 'offset';

    public const GROUP = 'group';

    public const GROUPS = 'groups';

    public static function toArray(): array
    {
        return array_values((new ReflectionClass(self::class))->getConstants());
    }
}
