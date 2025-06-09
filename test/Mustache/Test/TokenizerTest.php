<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group unit
 */
class Mustache_Test_TokenizerTest extends Yoast\PHPUnitPolyfills\TestCases\TestCase
{
    /**
     * @dataProvider getTokens
     */
    public function testScan($text, $delimiters, $expected)
    {
        $tokenizer = new Mustache_Tokenizer();
        $this->assertSame($expected, $tokenizer->scan($text, $delimiters));
    }

    public function testUnevenBracesThrowExceptions()
    {
        $tokenizer = new Mustache_Tokenizer();

        $this->expectException(Mustache_Exception_SyntaxException::class);
        $text = '{{{ name }}';
        $tokenizer->scan($text, null);
    }

    public function testUnevenBracesWithCustomDelimiterThrowExceptions()
    {
        $tokenizer = new Mustache_Tokenizer();

        $this->expectException(Mustache_Exception_SyntaxException::class);
        $text = '<%{ name %>';
        $tokenizer->scan($text, '<% %>');
    }

    public function getTokens()
    {
        return [
            [
                'text',
                null,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => 'text',
                    ],
                ],
            ],

            [
                'text',
                '<<< >>>',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => 'text',
                    ],
                ],
            ],

            [
                '{{ name }}',
                null,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'name',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            [
                '{{ name }}',
                '<<< >>>',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => '{{ name }}',
                    ],
                ],
            ],

            [
                '<<< name >>>',
                '<<< >>>',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'name',
                        Mustache_Tokenizer::OTAG  => '<<<',
                        Mustache_Tokenizer::CTAG  => '>>>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 12,
                    ],
                ],
            ],

            [
                "{{{ a }}}\n{{# b }}  \n{{= | | =}}| c ||/ b |\n|{ d }|",
                null,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_UNESCAPED,
                        Mustache_Tokenizer::NAME  => 'a',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 8,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => "\n",
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_SECTION,
                        Mustache_Tokenizer::NAME  => 'b',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 1,
                        Mustache_Tokenizer::INDEX => 18,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 1,
                        Mustache_Tokenizer::VALUE => "  \n",
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_DELIM_CHANGE,
                        Mustache_Tokenizer::LINE  => 2,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'c',
                        Mustache_Tokenizer::OTAG  => '|',
                        Mustache_Tokenizer::CTAG  => '|',
                        Mustache_Tokenizer::LINE  => 2,
                        Mustache_Tokenizer::INDEX => 37,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_END_SECTION,
                        Mustache_Tokenizer::NAME  => 'b',
                        Mustache_Tokenizer::OTAG  => '|',
                        Mustache_Tokenizer::CTAG  => '|',
                        Mustache_Tokenizer::LINE  => 2,
                        Mustache_Tokenizer::INDEX => 37,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 2,
                        Mustache_Tokenizer::VALUE => "\n",
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_UNESCAPED,
                        Mustache_Tokenizer::NAME  => 'd',
                        Mustache_Tokenizer::OTAG  => '|',
                        Mustache_Tokenizer::CTAG  => '|',
                        Mustache_Tokenizer::LINE  => 3,
                        Mustache_Tokenizer::INDEX => 51,
                    ],

                ],
            ],

            // See https://github.com/bobthecow/mustache.php/issues/183
            [
                '{{# a }}0{{/ a }}',
                null,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_SECTION,
                        Mustache_Tokenizer::NAME  => 'a',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 8,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => '0',
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_END_SECTION,
                        Mustache_Tokenizer::NAME  => 'a',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 9,
                    ],
                ],
            ],

            // custom delimiters don't swallow the next character, even if it is a }, }}}, or the same delimiter
            [
                '<% a %>} <% b %>%> <% c %>}}}',
                '<% %>',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'a',
                        Mustache_Tokenizer::OTAG  => '<%',
                        Mustache_Tokenizer::CTAG  => '%>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 7,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => '} ',
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'b',
                        Mustache_Tokenizer::OTAG  => '<%',
                        Mustache_Tokenizer::CTAG  => '%>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 16,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => '%> ',
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'c',
                        Mustache_Tokenizer::OTAG  => '<%',
                        Mustache_Tokenizer::CTAG  => '%>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 26,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => '}}}',
                    ],
                ],
            ],

            // unescaped custom delimiters are properly parsed
            [
                '<%{ a }%>',
                '<% %>',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_UNESCAPED,
                        Mustache_Tokenizer::NAME  => 'a',
                        Mustache_Tokenizer::OTAG  => '<%',
                        Mustache_Tokenizer::CTAG  => '%>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 9,
                    ],
                ],
            ],

            // Ensure that $arg token is not picked up during tokenization
            [
                '{{$arg}}default{{/arg}}',
                null,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_BLOCK_VAR,
                        Mustache_Tokenizer::NAME  => 'arg',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 8,
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_TEXT,
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::VALUE => 'default',
                    ],
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_END_SECTION,
                        Mustache_Tokenizer::NAME  => 'arg',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 15,
                    ],
                ],
            ],

            // Delimiters are trimmed
            [
                '<% name %>',
                ' <% %> ',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'name',
                        Mustache_Tokenizer::OTAG  => '<%',
                        Mustache_Tokenizer::CTAG  => '%>',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            // An empty string makes delimiters fall back to default
            [
                '{{ name }}',
                '',
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'name',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            // A bad delimiter type makes delimiters fall back to default
            [
                '{{ name }}',
                42,
                [
                    [
                        Mustache_Tokenizer::TYPE  => Mustache_Tokenizer::T_ESCAPED,
                        Mustache_Tokenizer::NAME  => 'name',
                        Mustache_Tokenizer::OTAG  => '{{',
                        Mustache_Tokenizer::CTAG  => '}}',
                        Mustache_Tokenizer::LINE  => 0,
                        Mustache_Tokenizer::INDEX => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getUnclosedTags
     */
    public function testUnclosedTagsThrowExceptions($text)
    {
        $tokenizer = new Mustache_Tokenizer();

        $this->expectException(Mustache_Exception_SyntaxException::class);
        $tokenizer->scan($text, null);
    }

    public function getUnclosedTags()
    {
        return [
            ['{{ name'],
            ['{{ name }'],
            ['{{{ name'],
            ['{{{ name }'],
            ['{{& name'],
            ['{{& name }'],
            ['{{# name'],
            ['{{# name }'],
            ['{{^ name'],
            ['{{^ name }'],
            ['{{/ name'],
            ['{{/ name }'],
            ['{{> name'],
            ['{{< name'],
            ['{{> name }'],
            ['{{< name }'],
            ['{{$ name'],
            ['{{$ name }'],
            ['{{= <% %>'],
            ['{{= <% %>='],
            ['{{= <% %>=}'],
            ['{{% name'],
            ['{{% name }'],
        ];
    }
}
