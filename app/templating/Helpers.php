<?php
/**
 * @author Tomáš Blatný
 */
namespace Tempeus\Templating;

use Nette\Bridges\ApplicationLatte\Template;
use Nette\Object;
use Nette\Utils\Html;

class Helpers extends Object {
	public static function prepareTemplate(Template $template)
	{
		$texy = new \Texy();
		$latte = $template->getLatte();
		$latte->addFilter('texy', function($text) use($texy) {
			return Html::el('')->setHtml($texy->process($text));
		});

		$latte->addFilter('time', function($text) {
			return date('j.n.Y G:i:s', $text);
		});

		$latte->addFilter('chatTime', function($text) {
			if(date('j.n.Y') !== date('j.n.Y', $text)) {
				return date('j.n. G:i', $text);
			} else {
				return date('G:i', $text);
			}
		});

		$latte->addFilter('timeSpent', function($text) {
			$time = (int) $text;
			$seconds = $time % 60;
			$time = (int)($time / 60);
			$minutes = $time % 60;
			$time = (int)($time / 60);
			$hours = $time;
			$times = [];
			$return = '';
			if($hours > 0) {
				$return .= $hours;
				if($hours === 1) {
					$return .= ' hodinu';
				} else {
					$return .= ' hodin' . ($hours < 5 ? 'y' : NULL);
				}
			}
			if($return !== '') {
				$times[] = $return;
				$return = '';
			}
			if($minutes > 0) {
				$return .= $minutes;
				if($minutes === 1) {
					$return .= ' minutu';
				} else {
					$return .= ' minut' . ($minutes < 5 ? 'y' : NULL);
				}
			}
			if($return !== '') {
				$times[] = $return;
				$return = '';
			}
			$return .= $seconds;
			if($seconds === 1) {
				$return .= ' sekundu';
			} else {
				$return .= ' sekund' . (($seconds < 5 && $seconds > 1) ? 'y' : NULL);
			}
			$times[] = $return;
			return implode(', ', $times);
		});
	}
}
