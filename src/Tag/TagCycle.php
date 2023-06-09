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

use Liquid\AbstractTag;
use Liquid\Exception\ParseException;
use Liquid\Liquid;
use Liquid\Context;
use Liquid\Regexp;
use Liquid\Variable;
use Liquid\FileSystem;

/**
 * Cycles between a list of values; calls to the tag will return each value in turn
 *
 * Example:
 *     {%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}
 *
 *     this will return:
 *     one two one
 *
 *     Cycles can also be named, to differentiate between multiple cycle with the same values:
 *     {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}
 *
 *     will return
 *     one one two two
 */
class TagCycle extends AbstractTag
{
	/**
	 * @var string The name of the cycle; if none is given one is created using the value list
	 */
	private $name;

	/**
	 * @var Variable[] The variables to cycle between
	 */
	private $variables = array();

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
		$simpleSyntax = preg_match_all('/(["\'])?(?(1)(?:(?!\1).)*+\1|[^\s,:|\'"]++)/', $markup, $matches);
		$namedSyntax = preg_match('/^(["\'])?(?(1)(?:(?!\1).)*+\1|[^\s,:|\'"]++)\s*\:/', $markup, $namedMatches);

		if ($namedSyntax) {
			$this->name = $matches[0][0];
			array_shift($matches[0]);
			$this->variables = $matches[0];
		} elseif ($simpleSyntax) {
			$this->variables = $matches[0];
			$this->name = "'" . implode($this->variables) . "'";
		} else {
			throw new ParseException("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @var Context $context
	 * @return string
	 */
	public function render(Context $context)
	{
		$context->push();

		$key = $context->get($this->name);

		if (isset($context->registers['cycle'][$key])) {
			$iteration = $context->registers['cycle'][$key];
		} else {
			$iteration = 0;
		}

		$result = $context->get($this->variables[$iteration]);

		$iteration += 1;

		if ($iteration >= count($this->variables)) {
			$iteration = 0;
		}

		$context->registers['cycle'][$key] = $iteration;

		$context->pop();

		return $result;
	}

	/**
	 * Returns the name of the block
	 *
	 * @return string
	 */
	public function blockName()
	{
		return 'cycle';
	}
}
