<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Feeds;

use Nette\Utils\Html;

class GtaFeedProvider extends BaseFeedProvider
{
	public function getInfo()
	{
		return Html::el('')->setHtml('Current map: <i>' . $this->getFeed()->map . '</i>');
	}
}
