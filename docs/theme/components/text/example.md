### Example Usage

```
$options = [
    Text::ATTRS => $description_attrs,
    Text::CLASSES => '',
    Text::TEXT => $this->panel_vars[ ImageTextPanel::FIELD_DESCRIPTION ],
];

$text_object = Text::factory( $options );

return $text_object->render();
```