# Конвертер XML в древовидную структуру

Этот код представляет собой класс `XmlConverter`, который позволяет преобразовывать XML в удобную древовидную структуру. Он может быть использован для проекта `blockly`, чтобы преобразовывать его данные в массив.

## Использование

1. Установите зависимости:

   ```bash
   Нет зависимостей
   ```

2. Подключите файл `XmlConverter.php` в свой проект:

   ```php
   require_once 'XmlConverter.php';
   ```

3. Создайте экземпляр класса `XmlConverter`:

   ```php
   $converter = new XmlConverter();
   ```

4. Загрузите XML-строку для преобразования:

   ```php
   $xmlString = '<xml xmlns="https://developers.google.com/blockly/xml">
     <!-- Ваш XML-код здесь -->
   </xml>';

   $converter->loadXmlString($xmlString);
   ```

5. Вызовите метод `convertXmlToTree()` для преобразования XML в древовидную структуру:

   ```php
   $converter->convertXmlToTree();
   ```

6. Результат будет выведен на экран в виде массивов. Массив `$resultArray` содержит данные, преобразованные из XML, а массив `$tree` представляет древовидную структуру данных.

## Пример

```php
$xmlString = '<xml xmlns="https://developers.google.com/blockly/xml">
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

$converter = new XmlConverter();
$converter->loadXmlString($xmlString);
$converter->convertXmlToTree();
```

## Лицензия

Этот код распространяется под лицензией [MIT](https://opensource.org/licenses/MIT).
