<?php
namespace Accessor;

class ArrayMutator extends DotNotationArrayObject
{
    /**
     * @param $index
     * @param $new
     * @return ArrayMutator
     */
    public function set($index, $new)
    {
        $array = $this->getArrayCopy();
        if(empty($array)) {
            $array = array($index => $new);
            $this->exchangeArray($array);
            return $this;
        }

        // already exists?
        if($this->offsetExists($index)) {
            $this->offsetSet($index, $new);
            return $this;
        }

        // doesn't exist and not a key chain?
        if (!$this->isKeyChain($index)) {
            $array[$index] = $new;
            return $this;
        }

        $this->setValue(explode(self::KEY_DELIMITER, $index), $new, $array, 1, self::DEFAULT_MAX_DEPTH);
        $this->exchangeArray($array);
        return $this;
    }

    /**
     * @param $keyChain
     * @param $newValue
     * @param $haystack
     * @param null $depth
     * @param null $maxDepth
     */
    private function setValue($keyChain, $newValue, &$haystack, $depth = null, $maxDepth = null)
    {
        if (!is_array($haystack)) {
            return;
        }

        if (empty($keyChain)) {
            return;
        }

        $currentIndex = 0;
        $isLast = false;
        $allElements = false;
        $temp =& $haystack;
        $this->assertMaxDepthLevelExceeded($depth, $maxDepth);
        $depth = is_numeric($depth) ? ($depth + 1) : 1 ;

        foreach ($keyChain as $idx => $key) {
            $currentIndex = $idx;
            $isLast = $idx === (count($keyChain) - 1);
            $key = trim($key);
            $actualKey = $this->extractActualKey($key, $pos = strpos($key, "["));
            if (!array_key_exists($actualKey, $temp)) {
                $temp[$actualKey] = array();
            }

            $temp =& $temp[$actualKey];
            if ($pos === false) {
                continue;
            }

            $innerIndex = $this->getArrayIndexFromKey($key);
            if (strlen($innerIndex) > 0) {
                if (!array_key_exists($innerIndex, $temp)) {
                    $temp[$innerIndex] = array();
                }

                $temp =& $temp[$innerIndex];
            } else {
                $allElements = true;
                break;
            }
        }

        if (!$allElements) {
            $temp = $newValue;
            return;
        }

        if (!$isLast) {
            foreach ($temp as $arrayIndex => &$element) {
                $this->setValue(array_slice($keyChain, $currentIndex + 1), $newValue, $element, $depth, $maxDepth);
            }
        } else {
            if (!is_array($temp)) {
                $temp = $newValue;
                return;
            }

            foreach ($temp as $arrayIndex => &$element) {
                $element = $newValue;
            }
        }
    }
}