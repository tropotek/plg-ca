<?php
namespace Ca\Form\Field;

use Ca\Db\CompetencyMap;
use Ca\Db\Option;
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
     * @throws \Exception
     */
    public static function createField($item)
    {
        $field = null;
        $name = 'item-'.$item->getId();
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
                $list = \Ca\Db\OptionMap::create()->findFiltered(array('scaleId' => $item->getScaleId()));
                $list = new \Tk\Form\Field\Option\ArrayObjectIterator($list, 'name', 'value');
                if ($item->getScale()->isMultiple()) {
                    $field = new CheckboxGroup($name, $list);
                    $field->addCss('ca-choice ca-multiple');
                } else {
                    $field = new Radio($name, $list);
                    $field->addCss('ca-choice');
                }
                break;
        }

        $field->setRequired($item->isRequired());

        if ($item->getDescription()) {
            $field->setNotes($item->getDescription());
        }
        $name = trim($item->getName());
        if (!$name) {
            /** @var Option $option */
            $option = CompetencyMap::create()->findFiltered(array('itemId' => $item->getId()))->current();
            if ($option) $name = $option->getName();
        }

        $field->setLabel($name);
        if (\Tk\Config::getInstance()->isDebug()) {
            $field->setLabel($name . ' [' . $item->getScale()->getName() . ']');
            //$field->setAttr('placeholder', $item->getScale()->getType());
        }

        if ($field) {
            if ($item->getName() || ($item->getCompetencyList()->count() > 1)) {
                $field->setCompetencyList($item->getCompetencyList());
            }
        }

        return $field;
    }

    /**
     *
     * @param Item $item
     * @param mixed $value
     */
    public static function isValid($item, $value)
    {
        if ($item->isRequired() && ($value === null || $value === '')) {
            return false;
        }
        switch ($item->getScale()->getType()) {
            case Scale::TYPE_TEXT:
            case Scale::TYPE_VALUE:
                break;
            case Scale::TYPE_CHOICE:
                if ($item->getScale()->isMultiple()) {
                    if ($item->isRequired() && (!is_array($value) || !count($value)))
                        return false;
                } else {
                }
                break;
        }
        return true;
    }
}