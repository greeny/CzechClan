<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Feeds;

use Nette\Utils\Html;

class MinecraftFeedProvider extends BaseFeedProvider
{
	public function getInfo()
	{
		return Html::el('')->setHtml('Minecraft verze <i>' . $this->getFeed()->version . '</i>');
	}
}
