<?php
namespace Accessor;

class DotNotationArrayObject extends \ArrayObject
{
    // the max depth level on recursive call
    const DEFAULT_MAX_DEPTH = 100;
    const KEY_DELIMITER = ".";

    /**
     * @param array $input
     * @param int $flags
     * @param string $iterator_class
     * @return $this
     */
    public static function create($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        return new static($input, $flags, $iterator_class);
    }

    /**
     * determine if the given key is a key chain by finding the '.' notation
     * @param string $key
     *
     * @return bool
     */
    protected function isKeyChain($key)
    {
        return strpos($key, ".") !== false;
    }

    /**
     * @param $key
     * @param $arraySignPos
     * @return bool|string
     */
    protected function extractActualKey($key, $arraySignPos)
    {
        // if so, remove the square brackets, otherwise the inner key itself is the real key
        return $arraySignPos === false ? $key : substr($key, 0, $arraySignPos);
    }

    /**
     * extract out the array index from square brackets, e.g. 'b[]' will be matched into $matches like ["[]", ""]
     *
     * @param string $key
     * @param bool $indexOnly only get the index value without brackets if set to true
     *
     * @return string
     */
    protected function getArrayIndexFromKey($key, $indexOnly = true)
    {
        // match square brackets
        preg_match("/\[([^\]]*)\]/", $key, $matches);
        // only need the index value without brackets and trim any single/double quotation mark
        return $indexOnly ? trim($matches[1], "'\"") : $matches[0];
    }

    /**
     * @param int $depth
     * @param int $maxDepth
     * @param string $message
     *
     * @throws \InvalidArgumentException
     */
    protected function assertMaxDepthLevelExceeded($depth, $maxDepth, $message = '')
    {
        if (is_null($depth) && is_null($maxDepth) && $depth > $maxDepth) {
            $message = $message ?: sprintf('the deepest recursive level has reached. max depth: %d', $maxDepth);
            throw new \InvalidArgumentException($message);
        }
    }
}