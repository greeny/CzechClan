<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Minecraft;

class MinecraftController
{
	/** @var array */
	protected $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getData($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
	}
}
