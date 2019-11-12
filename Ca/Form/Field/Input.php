<?php
namespace Ca\Form\Field;

use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class Input extends Field\Input
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
<div class="ca-control ca-input">
  <div class="" var="left-col"></div>
  <div class="">
      <input type="text" var="element" class="form-control" />
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}