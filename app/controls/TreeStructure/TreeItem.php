<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Controls\TreeStructure;

use Nette\Object;
use Tempeus\Model\ForumTopic;

class TreeItem extends Object
{
	/** @var ForumTopic */
	protected $topic;

	/** @var TreeItem|NULL */
	protected $parent;

	/** @var ForumTopic[] */
	protected $children = array();

	public function __construct(ForumTopic $topic, TreeItem $parent = NULL)
	{
		$this->topic = $topic;
		$this->parent = $parent;
	}

	/**
	 * @return TreeItem|NULL
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @return array of TreeItem
	 */
	public function getChildren()
	{
		usort($this->children, function(TreeItem $f1, TreeItem $f2) {
			return $f1->getTopic()->left < $f2->getTopic()->left ? -1 : 1;
		});
		return $this->children;
	}

	/**
	 * @return ForumTopic
	 */
	public function getTopic()
	{
		return $this->topic;
	}

	/**
	 * @param ForumTopic $topic
	 * @return TreeItem
	 */
	public function addChild(ForumTopic $topic)
	{
		return $this->children[] = new TreeItem($topic, $this);
	}

	/**
	 * @param ForumTopic $topic
	 * @return bool
	 */
	public function isChild(ForumTopic $topic)
	{
		return (bool) (($this->getTopic()->left < $topic->left) && ($this->getTopic()->right > $topic->right));
	}
}
