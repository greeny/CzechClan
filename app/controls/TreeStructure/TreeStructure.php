<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Controls\TreeStructure;

use Nette\Object;
use Tempeus\Model\ForumTopic;

class TreeStructure extends Object
{
	/**
	 * @param ForumTopic[] $topics
	 * @return TreeItem
	 */
	public static function create(array $topics)
	{
		$stack = new \SplStack();
		$root = NULL;
		$current = NULL;

		foreach($topics as $topic) {
			$stack->push($topic);
			if($root === NULL) {
				$current = $root = new TreeItem($topic);
			} else {
				while(!$current->isChild($topic)) {
					if(!$current = $current->getParent()) {
						break 2;
					}
				}
				$current = $current->addChild($stack->pop());
			}
		}

		return $root;
	}
}
