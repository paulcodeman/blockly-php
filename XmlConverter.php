<?php

$xmlString = '
<xml xmlns="https://developers.google.com/blockly/xml">
  <block type="menu" id="MUuB)i/fh6)O+5oH+d!l" x="175" y="97">
    <field name="name">1</field>
    <statement name="items">
      <block type="item" id="=aME%v8;4ZKl@E1xS0?]">
        <field name="text">цукцуа</field>
        <field name="href">/</field>
        <values name="properties">
          <block type="is_show" id="]{YEaC0iJ4/Co1rKVK.-">
            <field name="value">true</field>
          </block>
        </values>
      </block>
    </statement>
  </block>
  <block type="menu" id="eu*m7|fb}k]iTgc`sk]e" x="773" y="113">
    <field name="name">2</field>
    <statement name="items">
      <block type="item" id="fb=~#Sz-_oJt:5oT-AhE">
        <field name="text">ппппп</field>
        <field name="href">/</field>
      </block>
    </statement>
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
     * Loads an XML string.
     *
     * @param string $xmlString The XML string.
     */
    public function loadXmlString(string $xmlString): void
    {
        $this->dom->loadXML($xmlString);
    }

    /**
     * Converts XML to a multidimensional array.
     *
     * @param DOMElement $element The XML element.
     *
     * @return array An array containing data from the XML.
     */
    public function convertXmlToArray(DOMElement $element): array
    {
        $array = [];

        // Process element attributes
        if ($element->hasAttributes()) {
            $attributes = $element->attributes;

            foreach ($attributes as $attr) {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        // Process child elements
        if ($element->hasChildNodes()) {
            $children = $element->childNodes;

            foreach ($children as $child) {
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

        // Process text content of the element
        if ($element->hasChildNodes()) {
            $textNodes = array_filter(
                iterator_to_array($element->childNodes),
                function ($node) {
                    return $node->nodeType === XML_TEXT_NODE;
                }
            );

            if (count($textNodes) > 1) {
                $values = [];
                foreach ($textNodes as $textNode) {
                    $values[] = trim($textNode->nodeValue);
                }
                $array['value'] = $values;
            } elseif (count($textNodes) === 1) {
                $array['value'] = trim($textNodes[0]->nodeValue);
            }
        }

        return $array;
    }

    /**
     * Recursively processes the block structure.
     *
     * @param array $structure The block structure.
     *
     * @return array An array with the processed block structure.
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
                $array = $this->processBlocks($value['statement']);
                $value['statement'] = $array;
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
     * Converts the block structure to a tree structure.
     *
     * @param array $structure The block structure.
     *
     * @return array A tree structure of blocks.
     */
    public function createTree(array $structure): array
    {
        $result = [];

        if (isset($structure['block'])) {
            if (isset($structure['name'])) {
                $result['name'] = $structure['name'];
            }

            $result['block'] = [];

            if (isset($result['block'][0])) {
                foreach ($result['block'] as $value) {
                    if (isset($value['statement'])) {
                        $value['statement'] = $this->createTree($value['statement']);
                    }
                    $result['block'][] = $value;
                }
            } else {
                $value = $structure['block'];
                if (isset($value['statement'])) {
                    $array = $this->createTree($value['statement']);
                    $value['statement'] = $array;
                }
                if (isset($value[0])) {
                    foreach ($value as $item) {
                        $result['block'][] = $item;
                    }
                }
                else {
                    $result['block'][] = $value;
                }

                if (isset($value['next'])) {
                    $array = $this->createTree($value['next']);
                    unset($result['block'][count($result['block']) - 1]['next']);
                    foreach ($this->processBlocks($array) as $item) {
                        $result['block'][] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Converts XML to an array and outputs the result.
     */
    public function convertXmlToTree(): void
    {
        $root = $this->dom->documentElement;
        $resultArray = $this->convertXmlToArray($root);
        $tree = $this->createTree($resultArray);

        print_r($resultArray);
        print_r($tree);
    }
}

// Использование класса

$converter = new XmlConverter();
$converter->loadXmlString($xmlString);
$converter->convertXmlToTree();
