<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\AbstractBlock;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Captures the output inside a block and assigns it to a variable
 *
 * Example:
 *
 *     {% capture foo %} bar {% endcapture %}
 */
class TagCapture extends AbstractBlock
{
	/**
	 * The variable to assign to
	 *
	 * @var string
	 */
	private $to;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 * @param mixed $i
	 * @param mixed $n
	 *
	 * @throws \Liquid\Exception\ParseException
	 */
	public function __construct($i, $n, $markup, array &$tokens, FileSystem $fileSystem = null)
	{
		$syntax = preg_match('/\w+/', $markup, $matches);

		if ($syntax) {
			$this->to = $matches[0];
			parent::__construct($i, $n, $markup, $tokens, $fileSystem);
		} else {
			throw new ParseException("Syntax Error in 'capture' - Valid syntax: capture [var] [value]");
		}
	}

	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context)
	{
		$output = parent::render($context);

		$context->set($this->to, $output, true);
		return '';
	}

	/**
	 * Returns the name of the block
	 *
	 * @return string
	 */
	public function blockName()
	{
		return 'capture';
	}
}
