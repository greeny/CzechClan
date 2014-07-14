<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

use Nette\Utils\Strings;

class ArticleRepository extends BaseRepository
{
	public function findBySlug($slug)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[slug] = %s', $slug)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function fixSlug(Article $article)
	{
		$slug = $article->slug = Strings::webalize($article->title);
		$i = 1;
		while($this->findBySlug($article->slug)) {
			$article->slug = $slug . '-' . $i++;
		}
	}
}
 