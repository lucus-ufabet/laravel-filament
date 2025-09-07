<?php

namespace App\Support;

use App\Models\Website;
use App\Services\GoalServeClient;

class WebsiteRenderer
{
    /**
    * Compile the website's structure to a single-language JSON payload,
    * injecting middleware feeds where requested.
    */
    public static function render(Website $site, string $lang): array
    {
        $structure = $site->structure ?? [];
        // Backward compatibility if structure saved as ['sections' => [...]]
        if (isset($structure['sections']) && is_array($structure['sections'])) {
            $structure = $structure['sections'];
        }

        $result = [
            'website' => [
                'name' => $site->name,
                'slug' => $site->slug,
                'language' => $lang,
            ],
            'sections' => [],
        ];

        $goalServe = app(GoalServeClient::class);

        // Top-level `structure` is a Builder value: an array of blocks [{ type, data, id }]
        foreach (($structure ?? []) as $block) {
            [$type, $data] = self::normalizeBlock($block);
            switch ($type) {
                case 'h1':
                    $result['sections'][] = [
                        'type' => 'H1',
                        'text' => self::pickLang(($data['text'] ?? []), $lang),
                    ];
                    break;

                case 'h3':
                    $result['sections'][] = [
                        'type' => 'H3',
                        'text' => self::pickLang(($data['text'] ?? []), $lang),
                    ];
                    break;

                case 'h4':
                    $result['sections'][] = [
                        'type' => 'H4',
                        'text' => self::pickLang(($data['text'] ?? []), $lang),
                    ];
                    break;

                case 'h2':
                    $entry = [
                        'type' => 'H2',
                        'title' => self::pickLang(($data['title'] ?? []), $lang),
                        'components' => [],
                    ];

                    foreach (($data['components'] ?? []) as $component) {
                        [$ctype, $cdata] = self::normalizeBlock($component);
                        if ($ctype === 'unfold') {
                            $entry['components'][] = [
                                'type' => 'Unfold',
                                'items' => array_map(function ($item) use ($lang) {
                                    return [
                                        'title' => self::pickLang(($item['title'] ?? []), $lang),
                                        'body' => self::pickLang(($item['body'] ?? []), $lang),
                                    ];
                                }, $cdata['items'] ?? []),
                            ];
                        } elseif ($ctype === 'tabs') {
                            $entry['components'][] = [
                                'type' => 'Tabs',
                                'tabs' => array_map(function ($tab) use ($lang) {
                                    return [
                                        'label' => self::pickLang(($tab['label'] ?? []), $lang),
                                        'rows' => $tab['rows'] ?? [],
                                    ];
                                }, $cdata['tabs'] ?? []),
                            ];
                        } elseif ($ctype === 'column_tabs') {
                            $entry['components'][] = [
                                'type' => 'ColumnTabs',
                                'columns' => $cdata['columns'] ?? 2,
                                'tabs' => array_map(function ($tab) use ($lang) {
                                    return [
                                        'label' => self::pickLang(($tab['label'] ?? []), $lang),
                                        'rows' => $tab['rows'] ?? [],
                                    ];
                                }, $cdata['tabs'] ?? []),
                            ];
                        } elseif ($ctype === 'row_tabs') {
                            $entry['components'][] = [
                                'type' => 'RowTabs',
                                'rows' => array_map(function ($row) use ($lang) {
                                    return [
                                        'label' => self::pickLang(($row['label'] ?? []), $lang),
                                        'items' => $row['items'] ?? [],
                                    ];
                                }, $cdata['rows'] ?? []),
                            ];
                        }
                    }

                    // Inject middleware-driven components (e.g., live scores)
                    foreach (($data['middleware'] ?? []) as $mw) {
                        $mtype = strtolower($mw['type'] ?? '');
                        if ($mtype === 'livescore') {
                            $entry['components'][] = [
                                'type' => 'LiveScore',
                                'data' => $goalServe->liveScores()['data'] ?? [],
                            ];
                        } elseif ($mtype === 'betboost') {
                            $entry['components'][] = [
                                'type' => 'BetBoost',
                                'data' => $goalServe->betBoost()['data'] ?? [],
                            ];
                        } elseif ($mtype === 'gamesslot') {
                            $entry['components'][] = [
                                'type' => 'GamesSlot',
                                'data' => $goalServe->gamesSlot()['data'] ?? [],
                            ];
                        }
                    }

                    $result['sections'][] = $entry;
                    break;

                case 'unfold':
                    $result['sections'][] = [
                        'type' => 'Unfold',
                        'items' => array_map(function ($item) use ($lang) {
                            return [
                                'title' => self::pickLang(($item['title'] ?? []), $lang),
                                'body' => self::pickLang(($item['body'] ?? []), $lang),
                            ];
                        }, $data['items'] ?? []),
                    ];
                    break;

                case 'tabs':
                    $result['sections'][] = [
                        'type' => 'Tabs',
                        'tabs' => array_map(function ($tab) use ($lang) {
                            return [
                                'label' => self::pickLang(($tab['label'] ?? []), $lang),
                                'rows' => $tab['rows'] ?? [],
                            ];
                        }, $data['tabs'] ?? []),
                    ];
                    break;

                case 'column_tabs':
                    $result['sections'][] = [
                        'type' => 'ColumnTabs',
                        'columns' => $data['columns'] ?? 2,
                        'tabs' => array_map(function ($tab) use ($lang) {
                            return [
                                'label' => self::pickLang(($tab['label'] ?? []), $lang),
                                'rows' => $tab['rows'] ?? [],
                            ];
                        }, $data['tabs'] ?? []),
                    ];
                    break;

                case 'row_tabs':
                    $result['sections'][] = [
                        'type' => 'RowTabs',
                        'rows' => array_map(function ($row) use ($lang) {
                            return [
                                'label' => self::pickLang(($row['label'] ?? []), $lang),
                                'items' => $row['items'] ?? [],
                            ];
                        }, $data['rows'] ?? []),
                    ];
                    break;

                case 'tags':
                    $tagsField = $data['tags'] ?? [];
                    // Support both keyed array [lang => [..]] and repeater list [{lang, values}]
                    if (self::isAssoc($tagsField)) {
                        $tags = self::pickLangArray($tagsField, $lang);
                    } else {
                        $map = [];
                        foreach ($tagsField as $row) {
                            if (!empty($row['lang']) && !empty($row['values']) && is_array($row['values'])) {
                                $map[$row['lang']] = $row['values'];
                            }
                        }
                        $tags = self::pickLangArray($map, $lang);
                    }

                    // Middleware injections into tags (e.g., Live Score, BetBoost, Games Slot)
                    foreach (($data['middleware'] ?? []) as $mw) {
                        $mtype = strtolower($mw['type'] ?? '');
                        if ($mtype === 'livescore') {
                            $feed = $goalServe->liveScores();
                            if (! empty($feed['tags'])) {
                                $tags = array_values(array_unique(array_merge($tags, $feed['tags'])));
                            }
                        }
                        if ($mtype === 'betboost') {
                            $feed = $goalServe->betBoost();
                            if (! empty($feed['tags'])) {
                                $tags = array_values(array_unique(array_merge($tags, $feed['tags'])));
                            }
                        }
                        if ($mtype === 'gamesslot') {
                            $feed = $goalServe->gamesSlot();
                            if (! empty($feed['tags'])) {
                                $tags = array_values(array_unique(array_merge($tags, $feed['tags'])));
                            }
                        }
                    }

                    $result['sections'][] = [
                        'type' => 'Tags',
                        'tags' => $tags,
                    ];
                    break;

                case 'addcontent':
                case 'content':
                    $items = [];
                    foreach (($data['items'] ?? []) as $itemBlock) {
                        [$it, $idata] = self::normalizeBlock($itemBlock);
                        if (in_array($it, ['h3','h4','paragraph'], true)) {
                            $items[] = [
                                'type' => strtoupper($it),
                                'text' => self::pickLang(($idata['text'] ?? []), $lang),
                            ];
                        } elseif ($it === 'image') {
                            $items[] = [
                                'type' => 'Image',
                                'url' => $idata['url'] ?? null,
                                'alt' => self::pickLang(($idata['alt'] ?? []), $lang),
                            ];
                        }
                    }

                    $result['sections'][] = [
                        'type' => 'AddContent',
                        'items' => $items,
                    ];
                    break;

                case 'paragraph':
                    $result['sections'][] = [
                        'type' => 'Paragraph',
                        'text' => self::pickLang(($data['text'] ?? []), $lang),
                    ];
                    break;

                case 'image':
                    $result['sections'][] = [
                        'type' => 'Image',
                        'url' => $data['url'] ?? null,
                        'alt' => self::pickLang(($data['alt'] ?? []), $lang),
                    ];
                    break;
            }
        }

        return $result;
    }

    /**
     * Normalize a builder block to [type, data]. Accepts both raw arrays and already-normalized arrays.
     */
    private static function normalizeBlock(array $block): array
    {
        if (isset($block['type']) && isset($block['data']) && is_array($block['data'])) {
            return [strtolower((string) $block['type']), $block['data']];
        }
        // Treat as already the data for a named type
        $type = strtolower((string) ($block['type'] ?? ''));
        $data = $block['data'] ?? $block;
        return [$type, is_array($data) ? $data : []];
    }

    private static function pickLang(array $value, string $lang): ?string
    {
        // Accept either keyed arrays [lang => text] or repeater arrays [ ['lang'=>code, '{field}'=>value], ... ]
        if (self::isAssoc($value)) {
            if (array_key_exists($lang, $value)) {
                return $value[$lang];
            }
            if (array_key_exists('en', $value)) {
                return $value['en'];
            }
            return is_array($value) ? (reset($value) ?: null) : null;
        }

        // Repeater format
        $candidate = null;
        foreach ($value as $row) {
            if (! is_array($row) || ! isset($row['lang'])) continue;
            if ($row['lang'] === $lang) {
                return self::valueFromLangRow($row);
            }
            if ($row['lang'] === 'en') {
                $candidate = self::valueFromLangRow($row);
            }
        }
        return $candidate ?? (isset($value[0]) ? self::valueFromLangRow((array) $value[0]) : null);
    }

    private static function pickLangArray(array $value, string $lang): array
    {
        if (self::isAssoc($value)) {
            if (array_key_exists($lang, $value) && is_array($value[$lang])) {
                return $value[$lang];
            }
            if (array_key_exists('en', $value) && is_array($value['en'])) {
                return $value['en'];
            }
            return [];
        }

        // Repeater rows with 'lang' and array value field like 'values'
        $candidate = [];
        foreach ($value as $row) {
            if (! is_array($row) || ! isset($row['lang'])) continue;
            if ($row['lang'] === $lang) {
                return (array) self::valueFromLangRow($row);
            }
            if ($row['lang'] === 'en') {
                $candidate = (array) self::valueFromLangRow($row);
            }
        }
        return $candidate;
    }

    private static function isAssoc(array $arr): bool
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private static function valueFromLangRow(array $row): mixed
    {
        foreach ($row as $k => $v) {
            if ($k === 'lang') continue;
            return $v;
        }
        return null;
    }
}
