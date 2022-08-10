<?php
namespace Ca\Form\Field;

use Tk\Form\Field;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class CheckboxGroup extends Field\CheckboxGroup
{
    use Traits\CompetencyTrait;

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="checkbox-group" var="group">
  <div class="checkbox" repeat="option" var="option">
    <label var="label">
      <input type="checkbox" var="element" />
      <span var="text"></span>
    </label>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}