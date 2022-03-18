<?php
namespace Ca\Form\Field;

use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class Radio extends Field\Radio
{

    use Traits\CompetencyTrait;

    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->appendHtml('left-col', $this->getCompetencyHtml());

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="ca-control ca-radio">
  <div class="col-md-6" var="left-col"></div>
  <div class="col-md-6">
      <div class="radio" repeat="option" var="option">
        <label var="label">
          <input type="radio" var="element" /><br/>
          <span var="text"></span>
        </label>
      </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}