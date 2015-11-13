<?php
namespace Phalcon\Mvc\View\Engine\Volt;

/**
 * Phalcon\Mvc\View\Engine\Volt\MinifierCompiler
 *
 * Run basic html-minification on the compiled templates, taking
 * care to avoid minification of javascrip and other special cases.
 */
class MinifierCompiler extends Compiler
{
    /** @var bool $_minify */
    protected $minify = true;

    /**
     * Enable/disable minification
     * @param bool $shouldMinify
     */
    public function setMinify($shouldMinify)
    {
        $this->minify = $shouldMinify;
    }

    /**
     * Override _compileSource to minify the compiled output before it's stored in the cache.
     *
     * @inheritdoc
     */
    // @codingStandardsIgnoreStart
    protected function _compileSource($viewCode, $extendsMode = NULL)
    {
    // @codingStandardsIgnoreEnd
        $compilation = parent::_compileSource($viewCode, $extendsMode);

        // Check the original input for the no-minify comment, as it's stripped by the compiler.
        if ($this->minify === false || preg_match('/{# no-minify #}/', $viewCode)) {
            return $compilation;
        }

        if (is_array($compilation)) {
            foreach ($compilation as &$part) {
                if (is_string($part)) {
                    $part = $this->minify($part);
                } elseif (is_array($part)) {
                    foreach ($part as &$subPart) {
                        // Check for PHVOLT_T_RAW_FRAGMENT (type 357)
                        if (isset($subPart['type']) && $subPart['type'] == 357) {
                            $subPart['value'] = $this->minify($subPart['value']);
                        }
                    }
                }
            }
        } else {
            $compilation = $this->minify($compilation);
        }

        return $compilation;
    }

    /**
     * Get the minified value.
     *
     * All credit to Trevor Fitzgerald for the regex here.
     * See the original here: http://bit.ly/U7mv7a.
     *
     * @param string $block
     *
     * @return string
     */
    protected function minify($block)
    {
        if ($this->shouldMinify($block)) {
            $replace = [
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                '/<\?php/' => '<?php ',
                "/\n([\S])/" => ' $1',
                "/\r/" => '',
                "/\n/" => '',
                "/\t/" => ' ',
                '/ +/' => ' ',
            ];

            $block = preg_replace(array_keys($replace), array_values($replace), $block);
        }

        return $block;
    }

    /**
     * Determine if the block should be minified.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function shouldMinify($block)
    {
        return (
            !preg_match('/<(code|pre|textarea)/', $block) &&
            !preg_match('/<script[^\??>]*>[^<\/script>]/', $block) &&
            !preg_match('/value=("|\')(.*)([ ]{2,})(.*)("|\')/', $block)
        );
    }
}
