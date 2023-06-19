<?php

class XmlConverter
{
    private $dom;

    public function __construct()
    {
        $this->dom = new DOMDocument();
    }

    /**
     * Загружает XML-строку.
     *
     * @param string $xmlString XML-строка.
     */
    public function loadXmlString(string $xmlString): void
    {
        $this->dom->loadXML($xmlString);
    }

    /**
     * Преобразует XML в многомерный массив.
     *
     * @param DOMElement $element XML-элемент.
     *
     * @return array Массив с данными из XML.
     */
    public function convertXmlToArray(DOMElement $element): array
    {
        $array = [];

        // Обрабатываем атрибуты элемента
        if ($element->hasAttributes()) {
            $attributes = $element->attributes;

            foreach ($attributes as $attr) {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        // Обрабатываем дочерние элементы
        if ($element->hasChildNodes()) {
            $text = '';
            foreach ($element->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $nodeName = $child->nodeName;
                    $nodeValue = $this->convertXmlToArray($child);


                    if (empty($array[$nodeName])) {
                        $array[$nodeName] = $nodeValue;
                    } else {
                        if (empty($array[$nodeName][0])) {
                            $array[$nodeName] = [$array[$nodeName]];
                        }
                        $array[$nodeName][] = $nodeValue;
                    }

                }
                elseif ($child->nodeType === XML_TEXT_NODE) {
                    $text .= $child->nodeValue;
                }
            }
            $text = trim($text);
            if (!empty($text)) {
                $array['_'] = $text;
            }
        }

        return $array;
    }

    /**
     * Рекурсивно обрабатывает структуру блоков.
     *
     * @param array $structure Структура блоков.
     *
     * @return array Массив с обработанной структурой блоков.
     */
    public function processBlocks(array $structure): array
    {
        $result = [];

        if (isset($structure['block'][0])) {
            foreach ($structure['block'] as $value) {
                if (isset($value['statement'])) {
                    $value['statement'] = $this->processBlocks($value['statement']);
                }
                $result[] = $value;
            }
        } else {
            $value = $structure['block'];
            if (isset($value['statement'])) {
                $value['statement'] = $this->processBlocks($value['statement']);
            }
            $result[] = $value;
            if (isset($value['next'])) {
                $result[] = $this->processBlocks($value['next']);
            }
        }

        return $result;
    }
    /**
     * Преобразует структуру блоков в древовидную структуру.
     *
     * @param array $structure Структура блоков.
     *
     * @return array Древовидная структура блоков.
     */
    public function createTree(array $structure): array
    {
        $result = [];

        if (isset($structure['block'])) {
            if (isset($structure['block'][0])) {
                foreach ($structure['block'] as $block) {
                    $result[] = $this->createTree(['block' => $block]);
                }
            } else {
                if (isset($structure['name'])) {
                    $result['name'] = $structure['name'];
                }

                $result['block'] = [];

                if (isset($structure['block'][0])) {
                    foreach ($structure['block'] as $value) {
                        if (isset($value['statement'])) {
                            $value['statement'] = $this->createTree($value['statement']);
                        }
                        $result['block'][] = $value;
                    }
                } else {
                    $value = $structure['block'];
                    if (isset($value['statement'])) {
                        $value['statement'] = $this->createTree($value['statement']);
                    }
                    if (isset($value[0])) {
                        foreach ($value as $item) {
                            $result['block'][] = $item;
                        }
                    } else {
                        $result['block'][] = $value;
                    }

                    if (isset($value['next'])) {
                        $array = $this->createTree($value['next']);
                        unset($result['block'][count($result['block']) - 1]['next']);
                        $result['block'] = array_merge($result['block'], $this->processBlocks($array));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Преобразует XML в массив и выводит результат.
     */
    public function convertXmlToTree(): array
    {
        $root = $this->dom->documentElement;
        $resultArray = $this->convertXmlToArray($root);
        $tree = $this->createTree($resultArray);
        return $tree;
    }
}

