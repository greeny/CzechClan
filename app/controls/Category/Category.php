<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Controls;

use Nette\Application\UI\Control;

class Category extends Control
{
	public function render($category)
	{
		$this->template->setFile(__DIR__.'/category.latte');
		$this->template->category = $category;
		$this->template->render();
	}
}
