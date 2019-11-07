<?php
namespace Ca\Form\Field;

use Ca\Db\Scale;
use Ca\Db\Item;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class ItemHelper
{
    /**
     * @param Item $item
     * @return null|Field\Iface
     */
    public static function createField($item)
    {
        $field = null;
        $name = 'iid-'.$item->getId();
        switch ($item->getScale()->getType()) {
            case Scale::TYPE_TEXT:
                $field = new Textarea($name);
                $field->addCss('ca-text');
                break;
            case Scale::TYPE_VALUE:
                $field = new Input($name);
                $field->addCss('ca-value');
                break;
            case Scale::TYPE_CHOICE:
                $list = array('One' => 'one', 'Two' => 'two', 'Three' => 'three');
                if ($item->getScale()->isMultiple()) {
                    $field = new CheckboxGroup($name, $list);
                    $field->addCss('ca-group ca-checkbox');
                } else {
                    $field = new Radio($name, $list);
                    $field->addCss('ca-group ca-radio');
                }
                break;
        }
        if ($field) {
            if ($item->getName() || ($item->getCompetencyList()->count() > 1)) {
                $field->setCompetencyList($item->getCompetencyList());
            }
        }


        return $field;
    }

}