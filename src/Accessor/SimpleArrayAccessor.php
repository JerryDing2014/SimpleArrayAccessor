<?php
namespace Accessor;

class SimpleArrayAccessor
{
    // the max depth level on recursive call
    const DEFAULT_MAX_DEPTH = 100;

    /** @var array */
    private $array;

    /**
     * DotNotationAccessor constructor.
     * @param array $array
     */
    public function __construct(array $array = array())
    {
        $this->array = $array;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function setArray($array)
    {
        $this->array = $array;
        return $this;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        if ($this->isKeyChain($key)) {
            $notFound = 'not found';
            $value = $this->getValueByPath($key, $this->getArray(), $notFound);

            if (is_array($value)) {
                $result = array_filter($value, function ($v) {return $v !== 'not found';});
                return !empty($result);
            } else {
                return $value !== $notFound;
            }
        } else {
            return array_key_exists($key, $this->array);
        }
    }

    /**
     * @param string $key can be a key chain in a dot notation, such as:
     *                    "a",
     *                    "a.b",
     *                    "a.b[]",
     *                    "a.b[0]",
     *                    "a.b[].c",
     *                    "a.b[0].c",
     *                    "a.b[].c.d.e[].f.g"
     *                    "a.b[0].c.d.e[1].f.g"
     * @param null $defaultValue
     * @param int $maxDepth
     *
     * @return mixed|null
     */
    public function get($key, $defaultValue = null, $maxDepth = self::DEFAULT_MAX_DEPTH)
    {
        return $this->getValueByPath($key, $this->getArray(), $defaultValue, null, $maxDepth);
    }

    /**
     * @param string $key can be a key chain in a dot notation, such as:
     *                    "a",
     *                    "a.b",
     *                    "a.b[]",
     *                    "a.b[0]",
     *                    "a.b[].c",
     *                    "a.b[0].c",
     *                    "a.b[].c.d.e[].f.g"
     *                    "a.b[0].c.d.e[1].f.g"
     * @param mixed $value
     * @param int $maxDepth
     *
     * @return $this
     */
    public function set($key, $value, $maxDepth = self::DEFAULT_MAX_DEPTH)
    {
        $array = $this->getArray();
        $this->setValueByPath($key, $value, $array, null, $maxDepth);
        return $this->setArray($array);
    }

    /**
     * @param string|integer $key
     * @param array $array
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    private function getArrayValueByKey($key, array $array, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * get value by key or key chain
     *
     * Note: tis logic is similar to setValueByPath, so any changes made to it, they should also be made to
     * setValueByPath accordingly unless found a way to reuse them.
     *
     * @param string $key can be a key chain in a dot notation, such as:
     *                    "a",
     *                    "a.b",
     *                    "a.b[]",
     *                    "a.b[0]",
     *                    "a.b[].c",
     *                    "a.b[0].c",
     *                    "a.b[].c.d.e[].f.g"
     *                    "a.b[0].c.d.e[1].f.g"
     * @param mixed $haystack
     * @param null $defaultValue
     *
     * @param null $depth
     * @param int $maxDepth
     *
     * @return mixed|null
     * @throws \InvalidArgumentException only when the max recursive depth level has been exceeded
     */
    private function getValueByPath($key, $haystack = null, $defaultValue = null, $depth = null, $maxDepth = null)
    {
        if (empty($key)) {
            return $haystack;
        }

        if (empty($haystack)) {
            return $defaultValue;
        }

        if (!is_string($key) && !is_numeric($key)) {
            return $defaultValue;
        }

        if (!is_array($haystack)) {
            return $defaultValue;
        }

        if (array_key_exists($key, $haystack)) {
            return $haystack[$key];
        }

        // determine if the given key is a key chain concatenated with '.', e.g. 'a.b.c'
        if (!$this->isKeyChain($key)) {
            return $this->getArrayValueByKey($key, $haystack, $defaultValue);
        }

        // take a control on how depth this recursive process can go, if it goes beyond the allowed level
        // throw out exception
        $this->assertMaxDepthLevelExceeded($depth, $maxDepth);
        // let's start from 1 and keep increasing by 1 on every recursive call
        $depth = is_numeric($depth) ? ($depth + 1) : 1 ;

        $keys = explode(".", $key);
        $temp = $haystack;
        foreach ($keys as $index => $innerKey) {
            // is there any array associated in the key chain?
            $pos = strpos($innerKey, '[');
            // if so, remove the square brackets, otherwise the inner key itself is the real key
            $actualKey = $pos === false ? $innerKey : substr($innerKey, 0, $pos);

            // if key doesn't exist or the value key referred is not an array, then it should return the default value
            // given
            if (!is_array($temp) || !array_key_exists($actualKey, $temp)) {
                return $defaultValue;
            }

            $temp = $temp[$actualKey];

            // if there is no array associated like 'a.b.c', just simply get the value out.
            if ($pos === false) {
                continue;
            }

            // extract out the array index from square brackets, e.g. 'b[]' will be matched into $matches like ["[]", ""]
            $innerIndex= $this->getArrayIndexFromKey($innerKey);

            // re-form the rest part of key chain like 'c', 'c.d[].e' or 'c.d[1].e' so that it can be passed in again
            // recursively for handling the rest of keys
            $keyPath = $this->reformKeyChain(array_slice($keys, $index + 1));

            // no any keys afterwards (the last element)? directly return value, otherwise it will return default value
            // given if the value is not an array as expected
            if ($index == count($keys) - 1) {
                return strlen($innerIndex) == 0 ? $temp : $temp[$innerIndex];
            } else if (!is_array($temp)) {
                return $defaultValue;
            }

            // is there any index given specifically like 'b[0]'? if not, then put it into an iteration for obtaining
            // all values
            if (strlen($innerIndex) == 0) {
                $values = array();
                foreach ($temp as $arrayIndex => $subArray) {
                    // NOTE: recursive call will have memory exhausted if it goes too deep. However, in this configurator
                    // there is no such concern as the configuration key will not go beyond that deep, but it will still
                    // take control of it for edge cases' coverage perspective
                    $values[$arrayIndex] = $this->getValueByPath($keyPath, $subArray, $defaultValue, $depth, $maxDepth);
                }
                return $values;
            } else {
                // is the given index a valid value?
                if (!array_key_exists($innerIndex, $temp)) {
                    return $defaultValue;
                }

                return $this->getValueByPath($keyPath, $temp[$innerIndex], $defaultValue, $depth, $maxDepth);
            }
        }

        return $temp;
    }

    /**
     * set value by key path
     *
     * Note: its logic is similar to getValueByPath, so any changes made to it, they should also be made to
     * getValueByPath accordingly unless found a way to reuse them.
     *
     * @param string $key can be a key chain in a dot notation, such as:
     *                    "a",
     *                    "a.b",
     *                    "a.b[]",
     *                    "a.b[0]",
     *                    "a.b[].c",
     *                    "a.b[0].c",
     *                    "a.b[].c.d.e[].f.g"
     *                    "a.b[0].c.d.e[1].f.g"
     * @param mixed $value
     * @param mixed $haystack
     * @param integer|null $depth
     * @param int $maxDepth
     *
     * @return void
     * @throws \InvalidArgumentException only when the max recursive depth level has been exceeded
     */
    private function setValueByPath($key, $value, &$haystack = null, $depth = null, $maxDepth = null)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException("key is required");
        }

        if (is_null($haystack)) {
            $haystack = array($key => $value);
            return;
        }

        if (!is_string($key) && !is_numeric($key)) {
            return;
        }

        if (is_array($haystack) && array_key_exists($key, $haystack)) {
            $haystack[$key] = $value;
            return;
        }

        // determine if the given key is a key chain concatenated with '.', e.g. 'a.b.c'
        if (!$this->isKeyChain($key)) {
            return;
        }

        // take a control on how depth this recursive process can go, if it goes beyond the allowed level
        // throw out exception
        $this->assertMaxDepthLevelExceeded($depth, $maxDepth);
        // let's start from 1 and keep increasing by 1 on every recursive call
        $depth = is_numeric($depth) ? ($depth + 1) : 1 ;

        // always use a reference for assigning value
        $temp =& $haystack;

        $keys = explode(".", $key);
        foreach ($keys as $index => $innerKey) {
            // is there any array associated in the key chain?
            $pos = strpos($innerKey, '[');
            // if so, remove the square brackets, otherwise the inner key itself is the real key
            $actualKey = $pos === false ? $innerKey : substr($innerKey, 0, $pos);

            // if key doesn't exist or the value key referred is not an array, then it should return the default value
            // given
            if (!is_array($temp) || !array_key_exists($actualKey, $temp)) {
                return;
            }

            // get the reference of value the key refers to
            $temp =& $temp[$actualKey];


            // if there is no array associated like 'a.b.c', then continue
            if ($pos === false) {
                continue;
            }

            // extract out the array index from square brackets, e.g. 'b[]' will be matched into $matches like ["[]", ""]
            $innerIndex= $this->getArrayIndexFromKey($innerKey);

            // re-form the rest part of key chain like 'c', 'c.d[].e' or 'c.d[1].e' so that it can be passed in again
            // recursively for handling the rest of keys
            $keyPath = $this->reformKeyChain(array_slice($keys, $index + 1));

            // no any keys afterwards (the last element)? directly return value, otherwise it will return default value
            // given if the value is not an array as expected
            if ($index == count($keys) - 1) {
                if (strlen($innerIndex) != 0) {
                    $temp =& $temp[$innerIndex];
                }

                $temp = $value;
                return;
            } else if (!is_array($temp)) {
                return;
            }

            // is there any index given specifically like 'b[0]'? if not, then put it into an iteration for assigning
            // all values
            if (strlen($innerIndex) == 0) {
                foreach ($temp as $arrayIndex => &$subArray) {
                    // NOTE: recursive call will have memory exhausted if it goes too deep. However, in this configurator
                    // there is no such concern as the configuration key will not go beyond that deep, but it will still
                    // take control of it for edge cases' coverage perspective
                    $this->setValueByPath($keyPath, $value, $subArray, $depth, $maxDepth);
                }
                return;
            } else {
                // is the given index a valid value?
                if (!array_key_exists($innerIndex, $temp)) {
                    return;
                }

                $this->setValueByPath($keyPath, $value, $temp[$innerIndex], $depth, $maxDepth);
            }
        }

        // assign value by reference
        $temp = $value;
        return;
    }

    /**
     * extract out the array index from square brackets, e.g. 'b[]' will be matched into $matches like ["[]", ""]
     *
     * @param string $key
     * @param bool $indexOnly only get the index value without brackets if set to true
     *
     * @return string
     */
    private function getArrayIndexFromKey($key, $indexOnly = true)
    {
        // match square brackets
        preg_match("/\[([^\]]*)\]/", $key, $matches);
        // only need the index value without brackets and trim any single/double quotation mark
        return $indexOnly ? trim($matches[1], "'\"") : $matches[0];
    }

    /**
     * re-form the rest part of key chain like 'c', 'c.d[].e' or 'c.d[1].e' so that it can be passed in again
     * recursively for handling the rest of keys
     * @param array $keys
     *
     * @return string
     */
    private function reformKeyChain(array $keys)
    {
        return join('.', $keys);
    }

    /**
     * determine if the given key is a key chain by finding the '.' notation
     * @param string $key
     *
     * @return bool
     */
    private function isKeyChain($key)
    {
        return strpos($key, ".") !== false;
    }

    /**
     * @param int $depth
     * @param int $maxDepth
     * @param string $message
     *
     * @throws \InvalidArgumentException
     */
    private function assertMaxDepthLevelExceeded($depth, $maxDepth, $message = '')
    {
        if (is_null($depth) && is_null($maxDepth) && $depth > $maxDepth) {
            $message = $message ?: sprintf('the deepest recursive level has reached. max depth: %d', $maxDepth);
            throw new \InvalidArgumentException($message);
        }
    }
}