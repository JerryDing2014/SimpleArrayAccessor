<?php
namespace Accessor;

class ArrayAccessor extends DotNotationArrayObject
{
    /**
     * @param $index
     * @param null $default
     * @return array|mixed|null
     */
    public function get($index, $default = null)
    {
        $array = $this->getArrayCopy();
        if (empty($array)) {
            return $default;
        }

        if ($this->offsetExists($index)) {
            return $this->offsetGet($index);
        }

        if (!$this->isKeyChain($index)) {
            return $default;
        }

        return $this->getValue(explode(self::KEY_DELIMITER, $index), $array, $default, 1, self::DEFAULT_MAX_DEPTH);
    }

    /**
     * @param $keys
     * @param $haystack
     * @param null $default
     * @param null $depth
     * @param null $maxDepth
     * @return array|mixed|null
     */
    private function getValue($keys, $haystack, $default = null, $depth = null, $maxDepth = null)
    {
        // consider as not found if haystack is a primary types,e.g. string, numberic, object and ect, other than array
        if (!is_array($haystack)) {
            return $default;
        }

        $value = $haystack;
        if (empty($keys)) {
            return $value;
        }

        $this->assertMaxDepthLevelExceeded($depth, $maxDepth);
        $depth = is_numeric($depth) ? ($depth + 1) : 1 ;

        foreach ($keys as $idx => $key) {
            $isLast = $idx === (count($keys) - 1);
            $key = trim($key);
            $actualKey = $this->extractActualKey($key, $pos = strpos($key, "["));
            if (!array_key_exists($actualKey, $value)) {
                $value = $default;
                break;
            }

            $value = $value[$actualKey];
            if ($pos === false) {
                continue;
            }

            $innerIndex = $this->getArrayIndexFromKey($key);
            if (strlen($innerIndex) > 0) {
                if (!array_key_exists($innerIndex, $value)) {
                    $value = $default;
                    break;
                }

                $value = $value[$innerIndex];
            } else {
                if ($isLast) {
                    break;
                }

                $values = array();
                foreach ($value as $arrayIndex => $element) {
                    $values[$arrayIndex] = $this->getValue(array_slice($keys, $idx + 1), $element, $default, $depth, $maxDepth);
                }
                $value = $values;
                break;
            }
        }

        return $value;
    }
}