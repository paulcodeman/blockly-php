<?php

$xmlString = '
<xml xmlns="https://developers.google.com/blockly/xml">
  <block type="start" id="KYmqnsI).iwj_0iIIZ(M" x="429" y="217">
    <field name="selector">ul&gt;li.item</field>
    <next>
      <block type="duplicate" id="`|ayk)lp~ICkhmC)!INA">
        <next>
          <block type="duplicate" id="t-A[Lf/UtOa}b4atf;9_"></block>
        </next>
      </block>
    </next>
  </block>
  <block type="start" id="KX:6Su/%rhZM./Y4R*`4" x="802" y="211">
    <field name="selector">*.class</field>
    <next>
      <block type="duplicate" id="cDkr9o-JbAnC`sTYfeWI"></block>
    </next>
  </block>
</xml>';


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
            foreach ($element->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $nodeName = $child->nodeName;
                    $nodeValue = $this->convertXmlToArray($child);

                    if (!isset($array[$nodeName])) {
                        $array[$nodeName] = $nodeValue;
                    } else {
                        $array[$nodeName] = [$array[$nodeName]];
                        $array[$nodeName][] = $nodeValue;
                    }
                }
            }
        }

        // Обрабатываем текстовое содержимое элемента
        if ($element->hasChildNodes()) {
            $text = '';
            foreach ($element->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text .= $child->nodeValue;
                }
            }
            $text = trim($text);
            if (!empty($text)) {
                $array['value'] = $text;
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
                $array = $this->processBlocks($value['next']);
                unset($result[count($result) - 1]['next']);
                $result[] = $array;
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

// Использование класса

$converter = new XmlConverter();
$converter->loadXmlString($xmlString);
print_r($converter->convertXmlToTree());
